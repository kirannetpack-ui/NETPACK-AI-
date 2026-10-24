<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Delivery;
use App\Models\RiderDeposit; 
use App\Models\ReminderLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

     public function index()
    {
        $rider = Auth::user();
        $riderId = $rider->id;

        // Get wallet
        $wallet = Wallet::where('user_id', $riderId)->first();
        $balance = $wallet ? $wallet->balance : 0;

        // Get total earnings
        $totalEarned = Delivery::where('rider_id', $riderId)
            ->where('status', 'delivered')
            ->sum('delivery_fee');

        // Get deposit balance
        $depositBalance = $rider->rider_deposit_balance ?? 0;
        $depositLimit = $rider->rider_deposit_limit ?? 50000;

        // Get recent transactions
        $transactions = Transaction::where('wallet_id', $wallet->id ?? 0)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Get deposit history
        $depositHistory = RiderDeposit::where('rider_id', $riderId)
            ->where('type', 'deposit')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('rider.wallet', compact(
            'balance',
            'totalEarned',
            'depositBalance',
            'depositLimit',
            'transactions',
            'depositHistory'
        ));
    }
/**
     * Show deposit form
     */
    public function depositForm()
    {
        $rider = Auth::user();
        $depositBalance = $rider->rider_deposit_balance ?? 0;
        $depositLimit = $rider->rider_deposit_limit ?? 50000;
        $maxDeposit = $depositLimit - $depositBalance;

        return view('rider.deposit', compact('depositBalance', 'depositLimit', 'maxDeposit'));
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
            'payment_method' => 'required|in:bank,esewa,khalti,cash',
            'transaction_id' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $amount = $request->amount;
        $depositBalance = $rider->rider_deposit_balance ?? 0;
        $depositLimit = $rider->rider_deposit_limit ?? 50000;

        // Check if deposit exceeds limit
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
                'reference_type' => $request->payment_method,
                'reference_id' => $request->transaction_id,
                'description' => "Deposit of Rs. {$amount} via " . ucfirst($request->payment_method),
                'status' => 'completed',
                'verified_at' => now(),
                'verified_by' => $riderId,
                'metadata' => [
                    'payment_method' => $request->payment_method,
                    'remarks' => $request->remarks,
                ]
            ]);

            // Also add to wallet (for rider's earnings balance)
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $riderId],
                ['balance' => 0, 'pending_balance' => 0]
            );

            // Log transaction
            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'credit',
                'source' => 'deposit',
                'description' => "Deposit to rider wallet via " . ucfirst($request->payment_method),
                'status' => 'completed',
                'reference' => 'DEP-' . date('Ymd') . '-' . str_pad($deposit->id, 5, '0', STR_PAD_LEFT),
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance + $amount,
            ]);

            // Update wallet balance
            $wallet->balance += $amount;
            $wallet->save();

            DB::commit();

            // Notify admin about deposit
            $this->notifyAdminDeposit($rider, $amount, $request->payment_method);

            return redirect()->route('rider.wallet')
                ->with('success', "Rs. {$amount} deposited successfully! New balance: Rs. " . number_format($rider->rider_deposit_balance, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to process deposit: ' . $e->getMessage());
        }
    }

/**
 * Redirect to deposit page
 */
public function depositRedirect()
{
    return redirect()->route('rider.deposit');
}

    /**
     * Get wallet balance via API
     */
    public function getBalance()
    {
        $rider = Auth::user();
        $wallet = Wallet::where('user_id', $rider->id)->first();

        return response()->json([
            'wallet_balance' => $wallet ? $wallet->balance : 0,
            'deposit_balance' => $rider->rider_deposit_balance ?? 0,
            'deposit_limit' => $rider->rider_deposit_limit ?? 50000,
        ]);
    }

    /**
     * Get transaction history via API
     */
    public function getTransactions(Request $request)
    {
        $riderId = Auth::id();
        
        $wallet = Wallet::where('user_id', $riderId)->first();
        
        if (!$wallet) {
            return response()->json([]);
        }

        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 20))
            ->get();

        return response()->json($transactions);
    }

    /**
     * Get deposit history via API
     */
    public function getDepositHistory()
    {
        $riderId = Auth::id();
        
        $deposits = RiderDeposit::where('rider_id', $riderId)
            ->where('type', 'deposit')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($deposits);
    }

    /**
     * Notify admin about deposit
     */
    private function notifyAdminDeposit($rider, $amount, $method)
    {
        $admins = \App\Models\User::whereIn('user_type', ['admin', 'super_admin', 'domestic_admin'])->get();

        $message = "💰 RIDER DEPOSIT NOTIFICATION\n\n";
        $message .= "Rider: {$rider->name}\n";
        $message .= "Email: {$rider->email}\n";
        $message .= "Amount: Rs. {$amount}\n";
        $message .= "Payment Method: " . ucfirst($method) . "\n";
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
                    'payment_method' => $method,
                ]
            ]);
        }
    }
}
