<?php
// app/Http/Controllers/Api/RiderEarningsController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class RiderEarningsController extends Controller
{
    public function todayEarnings(Request $request)
    {
        $earnings = Transaction::whereHas('wallet', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })
        ->where('type', 'credit')
        ->where('source', 'delivery')
        ->whereDate('created_at', today())
        ->sum('amount');
        
        $deliveries = $request->user()->shipmentsAsRider()
            ->whereDate('delivered_at', today())
            ->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'earnings' => $earnings,
                'deliveries' => $deliveries
            ]
        ]);
    }
    
    public function weekEarnings(Request $request)
    {
        $earnings = Transaction::whereHas('wallet', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })
        ->where('type', 'credit')
        ->where('source', 'delivery')
        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->sum('amount');
        
        $deliveries = $request->user()->shipmentsAsRider()
            ->whereBetween('delivered_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'earnings' => $earnings,
                'deliveries' => $deliveries
            ]
        ]);
    }
    
    public function earningsHistory(Request $request)
    {
        $transactions = Transaction::whereHas('wallet', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })
        ->where('type', 'credit')
        ->where('source', 'delivery')
        ->latest()
        ->limit(50)
        ->get()
        ->map(function($transaction) {
            return [
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                'shipment_id' => $transaction->reference_id
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
    
    public function walletBalance(Request $request)
    {
        $wallet = $request->user()->wallet;
        
        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance,
                'total_earned' => $wallet->total_earned,
                'total_withdrawn' => $wallet->total_withdrawn
            ]
        ]);
    }
    
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500',
            'method' => 'required|in:bank,khalti,esewa'
        ]);
        
        $wallet = $request->user()->wallet;
        
        if ($wallet->balance < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance'
            ], 400);
        }
        
        try {
            $transaction = $wallet->debit(
                $request->amount,
                'withdrawal',
                null,
                "Withdrawal via " . $request->method
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted',
                'transaction_id' => $transaction->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal failed'
            ], 500);
        }
    }
}