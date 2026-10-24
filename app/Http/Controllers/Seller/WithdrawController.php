<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\SellerPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display withdrawal page
     */
    public function index()
    {
        $sellerId = Auth::id();
        
        $wallet = Wallet::where('user_id', $sellerId)->first();
        
        // Get payment methods
        $paymentMethods = SellerPaymentMethod::where('user_id', $sellerId)
            ->where('is_verified', true)
            ->get();
        
        // Get withdrawal history
        $withdrawals = Transaction::whereHas('wallet', function($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
        })->where('type', 'debit')
          ->where('source', 'withdrawal')
          ->orderBy('created_at', 'desc')
          ->paginate(20);
        
        // Stats
        $stats = [
            'total_withdrawn' => Transaction::whereHas('wallet', function($query) use ($sellerId) {
                $query->where('user_id', $sellerId);
            })->where('type', 'debit')
              ->where('source', 'withdrawal')
              ->sum('amount') ?? 0,
            'pending_withdrawals' => Transaction::whereHas('wallet', function($query) use ($sellerId) {
                $query->where('user_id', $sellerId);
            })->where('type', 'debit')
              ->where('source', 'withdrawal')
              ->where('status', 'pending')
              ->sum('amount') ?? 0,
            'total_count' => Transaction::whereHas('wallet', function($query) use ($sellerId) {
                $query->where('user_id', $sellerId);
            })->where('type', 'debit')
              ->where('source', 'withdrawal')
              ->count(),
        ];
        
        $balance = $wallet ? $wallet->balance : 0;
        
        return view('seller.withdraw.index', compact('wallet', 'withdrawals', 'stats', 'balance', 'paymentMethods'));
    }

    /**
     * Store withdrawal request
     */
    public function store(Request $request)
    {
        $sellerId = Auth::id();
        
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'payment_method_id' => 'required|exists:seller_payment_methods,id',
            'remarks' => 'nullable|string',
        ]);

        $wallet = Wallet::where('user_id', $sellerId)->first();
        
        if (!$wallet || $wallet->balance < $request->amount) {
            return redirect()->route('seller.withdraw')
                ->with('error', 'Insufficient balance. Available: Rs. ' . number_format($wallet->balance ?? 0, 2));
        }

        $paymentMethod = SellerPaymentMethod::where('user_id', $sellerId)
            ->where('id', $request->payment_method_id)
            ->where('is_verified', true)
            ->first();

        if (!$paymentMethod) {
            return redirect()->route('seller.withdraw')
                ->with('error', 'Payment method not found or not verified.');
        }

        DB::beginTransaction();

        try {
            // Create transaction record
            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => 'debit',
                'source' => 'withdrawal',
                'description' => 'Withdrawal request to ' . $paymentMethod->display_name,
                'status' => 'pending',
                'reference' => 'WTH-' . date('Ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance - $request->amount,
                'metadata' => [
                    'payment_method_id' => $paymentMethod->id,
                    'remarks' => $request->remarks,
                ],
            ]);

            // Deduct from wallet
            $wallet->pending_balance += $request->amount;
            $wallet->balance -= $request->amount;
            $wallet->save();

            DB::commit();

            return redirect()->route('seller.withdraw')
                ->with('success', 'Withdrawal request submitted successfully! Reference: ' . $transaction->reference);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('seller.withdraw')
                ->with('error', 'Failed to process withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Cancel withdrawal request
     */
    public function cancel($id)
    {
        $sellerId = Auth::id();
        
        $transaction = Transaction::whereHas('wallet', function($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
        })->where('source', 'withdrawal')
          ->where('status', 'pending')
          ->findOrFail($id);

        DB::beginTransaction();

        try {
            $wallet = $transaction->wallet;
            
            // Return funds to wallet
            $wallet->balance += $transaction->amount;
            $wallet->pending_balance -= $transaction->amount;
            $wallet->save();

            $transaction->update([
                'status' => 'cancelled',
                'description' => $transaction->description . ' (Cancelled)',
            ]);

            DB::commit();

            return redirect()->route('seller.withdraw')
                ->with('success', 'Withdrawal request cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('seller.withdraw')
                ->with('error', 'Failed to cancel withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Get withdrawal history
     */
    public function history(Request $request)
    {
        $sellerId = Auth::id();
        
        $query = Transaction::whereHas('wallet', function($q) use ($sellerId) {
            $q->where('user_id', $sellerId);
        })->where('type', 'debit')
          ->where('source', 'withdrawal');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('seller.withdraw.history', compact('withdrawals'));
    }
}
