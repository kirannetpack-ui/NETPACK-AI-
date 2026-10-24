<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\SellerPaymentMethod;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display wallet dashboard
     */
    public function index()
    {
        $sellerId = Auth::id();
        
        $walletAttributes = [
            'balance' => 0,
            'pending_balance' => 0,
            'total_earned' => 0,
            'total_withdrawn' => 0,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('wallets', 'user_type')) {
            $walletAttributes['user_type'] = 'seller';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('wallets', 'currency')) {
            $walletAttributes['currency'] = 'NPR';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('wallets', 'is_active')) {
            $walletAttributes['is_active'] = true;
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $sellerId],
            $walletAttributes
        );

        // Payment methods
        $paymentMethods = SellerPaymentMethod::where('user_id', $sellerId)->get();
        $defaultMethod = $paymentMethods->where('is_default', true)->first();
        $hasPaymentMethod = $paymentMethods->where('is_verified', true)->count() > 0;

        // Recent transactions
        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Recent payouts
        $recentPayouts = Payout::where('seller_id', $sellerId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Summary
        $summary = [
            'balance' => $wallet->balance ?? 0,
            'pending' => $wallet->pending_balance ?? 0,
            'total_earned' => Transaction::where('wallet_id', $wallet->id)->where('type', 'credit')->sum('amount') ?? 0,
            'total_withdrawn' => Transaction::where('wallet_id', $wallet->id)->where('type', 'debit')->where('source', 'withdrawal')->sum('amount') ?? 0,
            'total_payouts' => Payout::where('seller_id', $sellerId)->count(),
            'pending_payouts' => Payout::where('seller_id', $sellerId)->where('status', 'pending')->count(),
        ];

        return view('seller.wallet.index', compact(
            'wallet',
            'paymentMethods',
            'defaultMethod',
            'hasPaymentMethod',
            'transactions',
            'recentPayouts',
            'summary'
        ));
    }

    public function getBalance()
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['balance' => 0, 'pending_balance' => 0, 'currency' => 'NPR', 'is_active' => true]
        );

        return response()->json([
            'balance' => (float) $wallet->balance,
            'pending_balance' => (float) $wallet->pending_balance,
            'currency' => $wallet->currency ?: 'NPR',
        ]);
    }

    /**
     * Add payment method
     */
    public function addPaymentMethod(Request $request)
    {
        $sellerId = Auth::id();

        $request->validate([
            'method_type' => 'required|in:bank,esewa,khalti,connectips',
            'account_name' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        // If setting as default, remove default from others
        if ($request->has('is_default') && $request->is_default) {
            SellerPaymentMethod::where('user_id', $sellerId)->update(['is_default' => false]);
        }

        $data = [
            'user_id' => $sellerId,
            'method_type' => $request->method_type,
            'account_name' => $request->account_name,
            'is_default' => $request->has('is_default'),
            'is_verified' => false,
        ];

        // Bank specific fields
        if ($request->method_type === 'bank') {
            $request->validate([
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'branch' => 'nullable|string|max:255',
                'account_type' => 'nullable|in:savings,current',
            ]);
            $data['bank_name'] = $request->bank_name;
            $data['account_number'] = $request->account_number;
            $data['branch'] = $request->branch;
            $data['account_type'] = $request->account_type;
        }

        // eSewa specific fields
        if ($request->method_type === 'esewa') {
            $request->validate([
                'esewa_id' => 'required|string|max:50',
                'mobile_number' => 'required|string|max:15',
            ]);
            $data['esewa_id'] = $request->esewa_id;
            $data['mobile_number'] = $request->mobile_number;
        }

        // Khalti specific fields
        if ($request->method_type === 'khalti') {
            $request->validate([
                'khalti_id' => 'required|string|max:50',
                'mobile_number' => 'required|string|max:15',
            ]);
            $data['khalti_id'] = $request->khalti_id;
            $data['mobile_number'] = $request->mobile_number;
        }

        // ConnectIPS specific fields
        if ($request->method_type === 'connectips') {
            $request->validate([
                'connectips_id' => 'required|string|max:50',
            ]);
            $data['connectips_id'] = $request->connectips_id;
        }

        $paymentMethod = SellerPaymentMethod::create($data);

        return redirect()->route('seller.wallet')
            ->with('success', 'Payment method added successfully! Please wait for verification.');
    }

    /**
     * Delete payment method
     */
    public function deletePaymentMethod($id)
    {
        $sellerId = Auth::id();
        
        $method = SellerPaymentMethod::where('user_id', $sellerId)->findOrFail($id);
        
        if ($method->is_default) {
            return redirect()->route('seller.wallet')
                ->with('error', 'Cannot delete default payment method. Please set another as default first.');
        }
        
        $method->delete();

        return redirect()->route('seller.wallet')
            ->with('success', 'Payment method deleted successfully.');
    }

    /**
     * Set default payment method
     */
    public function setDefaultPaymentMethod($id)
    {
        $sellerId = Auth::id();
        
        $method = SellerPaymentMethod::where('user_id', $sellerId)->findOrFail($id);
        
        SellerPaymentMethod::where('user_id', $sellerId)->update(['is_default' => false]);
        $method->update(['is_default' => true]);

        return redirect()->route('seller.wallet')
            ->with('success', 'Default payment method updated successfully.');
    }

    /**
     * Request payout
     */
    public function requestPayout(Request $request)
    {
        $sellerId = Auth::id();
        
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'payment_method_id' => 'required|exists:seller_payment_methods,id',
            'remarks' => 'nullable|string',
        ]);

        $wallet = Wallet::where('user_id', $sellerId)->first();
        
        if (!$wallet || $wallet->balance < $request->amount) {
            return redirect()->route('seller.wallet')
                ->with('error', 'Insufficient balance. Available: Rs. ' . number_format($wallet->balance ?? 0, 2));
        }

        $paymentMethod = SellerPaymentMethod::where('user_id', $sellerId)
            ->where('id', $request->payment_method_id)
            ->where('is_verified', true)
            ->first();

        if (!$paymentMethod) {
            return redirect()->route('seller.wallet')
                ->with('error', 'Payment method not found or not verified.');
        }

        DB::beginTransaction();

        try {
            // Create payout request
            $payout = Payout::create([
                'seller_id' => $sellerId,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $request->amount,
                'fee' => $this->calculateFee($request->amount),
                'net_amount' => $request->amount - $this->calculateFee($request->amount),
                'status' => 'pending',
                'remarks' => $request->remarks,
                'reference_number' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
            ]);

            // Deduct from wallet
            $wallet->deductBalance(
                $request->amount,
                'Payout request #' . $payout->reference_number,
                'payout_request'
            );

            DB::commit();

            return redirect()->route('seller.wallet')
                ->with('success', 'Payout request submitted successfully! Reference: ' . $payout->reference_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('seller.wallet')
                ->with('error', 'Failed to process payout: ' . $e->getMessage());
        }
    }

    /**
     * Calculate payout fee
     */
    private function calculateFee($amount)
    {
        $feePercentage = 0.5; // 0.5% fee
        $fee = $amount * $feePercentage / 100;
        $minFee = 10;
        $maxFee = 500;
        
        return min(max($fee, $minFee), $maxFee);
    }
}
