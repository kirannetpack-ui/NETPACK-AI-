<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\RiderDeposit;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\ReminderLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show deposit form with payment methods
     */
    public function showDepositForm()
    {
        $rider = Auth::user();
        $riderId = $rider->id;

        // Get rider's payment methods - use a simple query
        $paymentMethods = PaymentMethod::where('user_id', $riderId)
            ->where('user_type', 'rider')
            ->where('is_verified', true)
            ->get();

        // If no payment methods, use empty collection
        if (!$paymentMethods || $paymentMethods->isEmpty()) {
            $paymentMethods = collect([]);
        }

        $depositBalance = $rider->rider_deposit_balance ?? 0;
        $depositLimit = $rider->rider_deposit_limit ?? 50000;
        $maxDeposit = $depositLimit - $depositBalance;

        // Return view with all data
        return view('rider.deposit', [
            'paymentMethods' => $paymentMethods,
            'depositBalance' => $depositBalance,
            'depositLimit' => $depositLimit,
            'maxDeposit' => $maxDeposit,
        ]);
    }

    /**
     * Process deposit request
     */
    public function deposit(Request $request)
    {
        $rider = Auth::user();
        $riderId = $rider->id;

        $request->validate([
            'amount' => 'required|numeric|min:100',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'transaction_id' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        // Get payment method
        $paymentMethod = PaymentMethod::where('user_id', $riderId)
            ->where('user_type', 'rider')
            ->where('id', $request->payment_method_id)
            ->first();

        if (!$paymentMethod) {
            return redirect()->back()
                ->with('error', 'Payment method not found or not verified.');
        }

        $amount = $request->amount;
        $depositBalance = $rider->rider_deposit_balance ?? 0;
        $depositLimit = $rider->rider_deposit_limit ?? 50000;

        if (($depositBalance + $amount) > $depositLimit) {
            return redirect()->back()
                ->with('error', "Deposit amount exceeds limit. Current balance: Rs. {$depositBalance}, Limit: Rs. {$depositLimit}");
        }

        DB::beginTransaction();

        try {
            // Update rider deposit balance
            $rider->rider_deposit_balance += $amount;
            $rider->save();

            // Create deposit record
            $deposit = RiderDeposit::create([
                'rider_id' => $riderId,
                'amount' => $amount,
                'balance' => $rider->rider_deposit_balance,
                'type' => 'deposit',
                'reference_type' => $paymentMethod->method_type,
                'reference_id' => $request->transaction_id,
                'description' => "Deposit of Rs. {$amount} via " . $paymentMethod->display_name,
                'status' => 'completed',
                'verified_at' => now(),
                'verified_by' => $riderId,
                'metadata' => [
                    'payment_method_id' => $paymentMethod->id,
                    'payment_method' => $paymentMethod->method_type,
                    'remarks' => $request->remarks,
                ]
            ]);

            // Add to wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $riderId],
                ['balance' => 0, 'pending_balance' => 0]
            );

            Transaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'credit',
                'source' => 'deposit',
                'description' => "Deposit to rider wallet via " . $paymentMethod->display_name,
                'status' => 'completed',
                'reference' => 'DEP-' . date('Ymd') . '-' . str_pad($deposit->id, 5, '0', STR_PAD_LEFT),
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance + $amount,
            ]);

            $wallet->balance += $amount;
            $wallet->save();

            DB::commit();

            // Notify admin
            $this->notifyAdminDeposit($rider, $amount, $paymentMethod);

            return redirect()->route('rider.wallet')
                ->with('success', "Rs. {$amount} deposited successfully via {$paymentMethod->display_name}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to process deposit: ' . $e->getMessage());
        }
    }

    /**
     * Notify admin about deposit
     */
    private function notifyAdminDeposit($rider, $amount, $paymentMethod)
    {
        $admins = \App\Models\User::whereIn('user_type', ['admin', 'super_admin', 'domestic_admin'])->get();

        $message = "💰 RIDER DEPOSIT NOTIFICATION\n\n";
        $message .= "Rider: {$rider->name}\n";
        $message .= "Email: {$rider->email}\n";
        $message .= "Amount: Rs. {$amount}\n";
        $message .= "Payment Method: {$paymentMethod->display_name}\n";
        $message .= "New Balance: Rs. " . number_format($rider->rider_deposit_balance, 2) . "\n";
        $message .= "Time: " . now()->format('Y-m-d H:i:s');

        foreach ($admins as $admin) {
            ReminderLog::create([
                'pickup_request_id' => null,
                'reminder_id' => null,
                'reminder_type' => 'rider_deposit',
                'sent_to' => $admin->email,
                'message' => $message,
                'channel' => 'database',
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'rider_id' => $rider->id,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod->method_type,
                ]
            ]);
        }
    }
}
