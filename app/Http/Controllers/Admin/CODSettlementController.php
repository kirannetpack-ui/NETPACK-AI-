<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CODSettlement;
use App\Models\Order;
use App\Models\User;
use App\Models\ReminderLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CODSettlementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin,domestic_admin']);
    }

    /**
     * List all COD settlements
     */
    public function index(Request $request)
    {
        $query = CODSettlement::with(['order', 'seller', 'rider']);

        if ($request->filled('status')) {
            $query->where('settlement_status', $request->status);
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $settlements = $query->orderBy('created_at', 'desc')->paginate(20);
        $sellers = User::where('user_type', 'seller')->get();

        $stats = [
            'total' => CODSettlement::count(),
            'pending' => CODSettlement::where('settlement_status', 'pending')->count(),
            'processing' => CODSettlement::where('settlement_status', 'processing')->count(),
            'completed' => CODSettlement::where('settlement_status', 'completed')->count(),
            'total_amount' => CODSettlement::sum('cod_amount'),
        ];

        return view('admin.cod-settlements.index', compact('settlements', 'sellers', 'stats'));
    }

    /**
     * Show COD settlement details
     */
    public function show($id)
    {
        $settlement = CODSettlement::with(['order', 'order.items', 'seller', 'rider', 'verifiedBy'])
            ->findOrFail($id);

        return view('admin.cod-settlements.show', compact('settlement'));
    }

    /**
     * Update settlement status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,failed',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            $newStatus = $request->status;
            $settlement = CODSettlement::whereKey($id)->lockForUpdate()->firstOrFail();
            $oldStatus = $settlement->settlement_status;
            $allowedTransitions = [
                'pending' => ['processing', 'failed'],
                'processing' => ['completed', 'failed'],
                'completed' => [],
                'failed' => [],
            ];

            if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [], true)) {
                DB::rollBack();
                return redirect()->back()->with('error', "Settlement cannot move from {$oldStatus} to {$newStatus}.");
            }

            $settlement->update([
                'settlement_status' => $newStatus,
                'remarks' => $request->remarks,
                'verified_at' => $newStatus === 'completed' ? now() : $settlement->verified_at,
                'verified_by' => $newStatus === 'completed' ? Auth::id() : $settlement->verified_by,
                'settlement_date' => $newStatus === 'completed' ? now() : $settlement->settlement_date,
            ]);

            // Update order status
            $order = Order::whereKey($settlement->order_id)->lockForUpdate()->first();
            if ($order) {
                $order->update([
                    'settlement_status' => $newStatus,
                    'cod_status' => $newStatus === 'completed' ? 'settled' : ($newStatus === 'failed' ? 'failed' : 'collected'),
                    'cod_verified_at' => $newStatus === 'completed' ? now() : $order->cod_verified_at,
                    'cod_verified_by' => $newStatus === 'completed' ? Auth::id() : $order->cod_verified_by,
                ]);

                // If completed, release payments
                if ($newStatus === 'completed' && $oldStatus === 'processing') {
                    $this->releasePayments($settlement, $order);
                }
            }

            // Log the status change
            ReminderLog::create([
                'pickup_request_id' => null,
                'reminder_id' => null,
                'reminder_type' => 'cod_status_change',
                'sent_to' => 'system',
                'message' => "COD Settlement #{$settlement->id} status changed from {$oldStatus} to {$newStatus}",
                'channel' => 'database',
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'settlement_id' => $settlement->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]
            ]);

            DB::commit();

            return redirect()->route('admin.cod-settlements.index')
                ->with('success', "Settlement status updated from {$oldStatus} to {$newStatus}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update settlement: ' . $e->getMessage());
        }
    }

    /**
     * Release payments to seller and rider
     */
    private function releasePayments($settlement, $order)
    {
        // 1. Release to Seller
        $sellerWallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $settlement->seller_id],
            ['balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
        );

        $sellerWallet->addBalance(
            $settlement->seller_amount,
            "COD settlement for Order #{$order->order_number}",
            'cod_settlement'
        );

        // 2. Release to Rider
        if ($settlement->rider_id) {
            $riderWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $settlement->rider_id],
                ['balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
            );

            $riderWallet->addBalance(
                $settlement->rider_amount,
                "COD delivery fee for Order #{$order->order_number}",
                'cod_delivery'
            );
        }

        // 3. Admin margin goes to the actual configured administrator, never
        // an assumed user ID.
        $admin = User::whereIn('user_type', ['super_admin', 'admin', 'domestic_admin'])
            ->orderByRaw("case when user_type = 'super_admin' then 0 when user_type = 'admin' then 1 else 2 end")
            ->first();

        if (!$admin) {
            throw new \RuntimeException('No administrator is available to receive the COD margin.');
        }

        $adminWallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $admin->id],
            ['balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
        );

        $adminWallet->addBalance(
            $settlement->margin_amount,
            "Admin margin for Order #{$order->order_number}",
            'admin_margin'
        );

        if ($settlement->rider_id) {
            User::whereKey($settlement->rider_id)->increment('total_earnings', $settlement->rider_amount);
        }

        // Notify all parties
        $this->notifySettlementComplete($settlement, $order);
    }

    /**
     * Notify all parties about settlement
     */
    private function notifySettlementComplete($settlement, $order)
    {
        $message = "✅ COD SETTLEMENT COMPLETED\n\n";
        $message .= "Order: #{$order->order_number}\n";
        $message .= "COD Amount: Rs. {$settlement->cod_amount}\n";
        $message .= "Seller Amount: Rs. {$settlement->seller_amount}\n";
        $message .= "Rider Amount: Rs. {$settlement->rider_amount}\n";
        $message .= "Admin Margin: Rs. {$settlement->margin_amount}\n";
        $message .= "Settlement Reference: {$settlement->settlement_reference}\n";
        $message .= "Completed at: " . now()->format('Y-m-d H:i:s');

        // Notify seller
        $seller = User::find($settlement->seller_id);
        if ($seller) {
            ReminderLog::create([
                'pickup_request_id' => null,
                'reminder_id' => null,
                'reminder_type' => 'seller_payment',
                'sent_to' => $seller->email,
                'message' => "COD payment of Rs. {$settlement->seller_amount} has been credited to your wallet for Order #{$order->order_number}",
                'channel' => 'database',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }

        // Notify rider
        if ($settlement->rider_id) {
            $rider = User::find($settlement->rider_id);
            if ($rider) {
                ReminderLog::create([
                    'pickup_request_id' => null,
                    'reminder_id' => null,
                    'reminder_type' => 'rider_payment',
                    'sent_to' => $rider->email,
                    'message' => "COD delivery fee of Rs. {$settlement->rider_amount} has been credited to your wallet for Order #{$order->order_number}",
                    'channel' => 'database',
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
        }
    }

    /**
     * Export settlements report
     */
    public function export(Request $request)
    {
        $query = CODSettlement::with(['order', 'seller']);

        if ($request->filled('status')) {
            $query->where('settlement_status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $settlements = $query->get();

        if ($settlements->count() === 0) {
            return redirect()->route('admin.cod-settlements.index')
                ->with('error', 'No settlements found to export.');
        }

        $filename = "cod_settlements_report_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://temp', 'w+');

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Settlement ID',
            'Order Number',
            'Seller',
            'Rider',
            'COD Amount',
            'Delivery Charge',
            'Admin Margin',
            'Seller Amount',
            'Rider Amount',
            'Status',
            'Created At',
            'Settled At',
        ]);

        foreach ($settlements as $settlement) {
            fputcsv($handle, [
                $settlement->id,
                $settlement->order->order_number ?? 'N/A',
                $settlement->seller->name ?? 'N/A',
                $settlement->rider->name ?? 'N/A',
                number_format($settlement->cod_amount, 2),
                number_format($settlement->delivery_charge, 2),
                number_format($settlement->admin_margin, 2),
                number_format($settlement->seller_amount, 2),
                number_format($settlement->rider_amount, 2),
                $settlement->settlement_status,
                $settlement->created_at->format('Y-m-d H:i:s'),
                $settlement->settlement_date ? $settlement->settlement_date->format('Y-m-d H:i:s') : 'N/A',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename={$filename}")
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Pragma', 'public');
    }
}
