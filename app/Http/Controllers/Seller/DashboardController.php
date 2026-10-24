<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the seller dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sellerId = auth()->id();
        $user = Auth::user();
        
        // =============================================
        // WALLET & BALANCE
        // =============================================
        $wallet = Wallet::where('user_id', $sellerId)->first();
        $balance = $wallet->balance ?? 0;
        $pendingBalance = $wallet->pending_balance ?? 0;
        
        // =============================================
        // EARNINGS SUMMARY
        // =============================================
        $earnings = [
            'today' => $this->getEarningsForPeriod($sellerId, 'today'),
            'week' => $this->getEarningsForPeriod($sellerId, 'week'),
            'month' => $this->getEarningsForPeriod($sellerId, 'month'),
            'total' => $this->getEarningsForPeriod($sellerId, 'total')
        ];
        
        // Individual earnings for dashboard cards
        $todayEarnings = $earnings['today']['amount'] ?? 0;
        $todayTransactions = $earnings['today']['count'] ?? 0;
        $weekEarnings = $earnings['week']['amount'] ?? 0;
        $weekTransactions = $earnings['week']['count'] ?? 0;
        
        // =============================================
        // SHIPMENT & DISPATCH STATISTICS
        // =============================================
        $shipmentStats = [
            'total' => Shipment::where('seller_id', $sellerId)->count(),
            'active' => Shipment::where('seller_id', $sellerId)->whereIn('status', ['pending', 'processing', 'in_transit', 'out_for_delivery'])->count(),
            'delivered' => Shipment::where('seller_id', $sellerId)->where('status', 'delivered')->count(),
        ];
        
        $productStats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'low_stock' => 0];
        $totalProducts = 0;
        $activeProducts = 0;
        
        // =============================================
        // ORDER STATISTICS (Check if Order model exists)
        // =============================================
        $orderStats = [
            'total' => 0,
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'cancelled' => 0,
        ];
        
        // Only try to get order stats if the Order model exists
        if (class_exists('App\Models\Order')) {
            $orderStats = [
                'total' => \App\Models\Order::where('seller_id', $sellerId)->count(),
                'pending' => \App\Models\Order::where('seller_id', $sellerId)->where('status', 'pending')->count(),
                'processing' => \App\Models\Order::where('seller_id', $sellerId)->where('status', 'processing')->count(),
                'completed' => \App\Models\Order::where('seller_id', $sellerId)->where('status', 'completed')->count(),
                'cancelled' => \App\Models\Order::where('seller_id', $sellerId)->where('status', 'cancelled')->count(),
            ];
        }
        
        // =============================================
        // RECENT SHIPMENTS
        // =============================================
        $recentShipments = Shipment::where('seller_id', $sellerId)
            ->with(['tracking', 'payment'])
            ->latest()
            ->limit(10)
            ->get();
        
        // =============================================
        // RECENT TRANSACTIONS
        // =============================================
        $recentTransactions = Transaction::whereHas('wallet', function($q) use ($sellerId) {
            $q->where('user_id', $sellerId);
        })
        ->with(['wallet'])
        ->latest()
        ->limit(10)
        ->get();
        
        // =============================================
        // CHART DATA (Last 6 Months)
        // =============================================
        $chartData = $this->getMonthlyEarnings($sellerId);
        $chartLabels = $chartData['months'] ?? [];
        $chartEarnings = $chartData['earnings'] ?? [];
        
        // =============================================
        // NOTIFICATIONS COUNT
        // =============================================
        $unreadNotifications = $user->unreadNotifications->count();
        
        // =============================================
        // RECENT ACTIVITIES
        // =============================================
        $recentActivities = $this->getRecentActivities($sellerId);
        
        $topProducts = collect();
        
        return view('seller.dashboard', compact(
            'wallet',
            'balance',
            'pendingBalance',
            'earnings',
            'todayEarnings',
            'todayTransactions',
            'weekEarnings',
            'weekTransactions',
            'productStats',
            'totalProducts',
            'activeProducts',
            'orderStats',
            'shipmentStats',
            'recentShipments',
            'recentTransactions',
            'chartLabels',
            'chartEarnings',
            'unreadNotifications',
            'recentActivities',
            'topProducts'
        ));
    }
    
    /**
     * Get earnings for a specific period.
     *
     * @param int $sellerId
     * @param string $period
     * @return array
     */
    private function getEarningsForPeriod($sellerId, $period)
    {
        $query = Transaction::whereHas('wallet', function($q) use ($sellerId) {
            $q->where('user_id', $sellerId);
        })->where('type', 'credit');
        
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'total':
                // No date filter
                break;
        }
        
        return [
            'amount' => $query->sum('amount') ?? 0,
            'count' => $query->count() ?? 0
        ];
    }
    
    /**
     * Get monthly earnings for chart.
     *
     * @param int $sellerId
     * @return array
     */
    private function getMonthlyEarnings($sellerId)
    {
        $months = [];
        $earnings = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M');
            
            $amount = Transaction::whereHas('wallet', function($q) use ($sellerId) {
                $q->where('user_id', $sellerId);
            })
            ->where('type', 'credit')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->sum('amount') ?? 0;
            
            $earnings[] = $amount;
        }
        
        return [
            'months' => $months,
            'earnings' => $earnings
        ];
    }
    
    /**
     * Get recent activities.
     *
     * @param int $sellerId
     * @return \Illuminate\Support\Collection
     */
    private function getRecentActivities($sellerId)
    {
        $activities = collect();
        
        // Get recent orders if Order model exists
        if (class_exists('App\Models\Order')) {
            $orders = \App\Models\Order::where('seller_id', $sellerId)
                ->latest()
                ->limit(5)
                ->get()
                ->map(function($order) {
                    return (object) [
                        'type' => 'order',
                        'title' => 'New Order #' . ($order->order_number ?? $order->id),
                        'description' => 'Order placed by ' . ($order->customer_name ?? 'Customer'),
                        'time' => $order->created_at->diffForHumans(),
                        'icon' => 'shopping-cart',
                        'color' => 'blue'
                    ];
                });
            
            $activities = $activities->merge($orders);
        }
        
        // Get recent shipments
        $shipments = Shipment::where('seller_id', $sellerId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($shipment) {
                return (object) [
                    'type' => 'shipment',
                    'title' => 'Shipment #' . ($shipment->tracking_number ?? $shipment->id),
                    'description' => 'Status: ' . ucfirst($shipment->status ?? 'pending'),
                    'time' => $shipment->created_at->diffForHumans(),
                    'icon' => 'truck',
                    'color' => 'green'
                ];
            });
        
        // Get recent transactions
        $transactions = Transaction::whereHas('wallet', function($q) use ($sellerId) {
            $q->where('user_id', $sellerId);
        })
        ->latest()
        ->limit(5)
        ->get()
        ->map(function($transaction) {
            return (object) [
                'type' => 'transaction',
                'title' => $transaction->type === 'credit' ? 'Payment Received' : 'Payment Made',
                'description' => 'Amount: ₱' . number_format($transaction->amount ?? 0, 2),
                'time' => $transaction->created_at->diffForHumans(),
                'icon' => $transaction->type === 'credit' ? 'arrow-down' : 'arrow-up',
                'color' => $transaction->type === 'credit' ? 'green' : 'red'
            ];
        });
        
        // Merge and sort by time
        $activities = $activities->merge($shipments)->merge($transactions)
            ->sortByDesc(function($item) {
                return strtotime($item->time ?? now());
            })
            ->take(10);
        
        return $activities;
    }
    
    /**
     * Handle withdrawal request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'payout_method' => 'required|in:bank,mobile',
            'bank_name' => 'required_if:payout_method,bank|string|max:255',
            'account_number' => 'required_if:payout_method,bank|string|max:255',
            'account_name' => 'required_if:payout_method,bank|string|max:255',
            'mobile_number' => 'required_if:payout_method,mobile|string|max:20'
        ]);
        
        $wallet = Wallet::where('user_id', auth()->id())->first();
        
        if (!$wallet || $wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance');
        }
        
        DB::beginTransaction();
        
        try {
            // Create withdrawal transaction
            $transaction = $wallet->debit(
                $request->amount,
                'withdrawal',
                null,
                "Withdrawal request - " . ucfirst($request->payout_method)
            );
            
            DB::commit();
            
            return back()->with('success', 'Withdrawal request submitted successfully! We will process it within 24-48 hours.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Withdrawal failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Update bank details.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBankDetails(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
            'bank_branch' => 'nullable|string|max:255'
        ]);
        
        $wallet = Wallet::where('user_id', auth()->id())->first();
        
        if ($wallet) {
            $wallet->update([
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,
                'bank_branch' => $request->bank_branch
            ]);
        } else {
            // Create wallet if it doesn't exist
            Wallet::create([
                'user_id' => auth()->id(),
                'balance' => 0,
                'pending_balance' => 0,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,
                'bank_branch' => $request->bank_branch
            ]);
        }
        
        return back()->with('success', 'Bank details updated successfully!');
    }
    
    /**
     * Get seller statistics for API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $sellerId = auth()->id();
        
        $stats = [
            'balance' => Wallet::where('user_id', $sellerId)->value('balance') ?? 0,
            'pending' => Wallet::where('user_id', $sellerId)->value('pending_balance') ?? 0,
            'products' => Product::where('user_id', $sellerId)->count(),
            'orders' => class_exists('App\Models\Order') ? \App\Models\Order::where('seller_id', $sellerId)->count() : 0,
            'shipments' => Shipment::where('seller_id', $sellerId)->count(),
            'earnings' => [
                'today' => $this->getEarningsForPeriod($sellerId, 'today'),
                'week' => $this->getEarningsForPeriod($sellerId, 'week'),
                'month' => $this->getEarningsForPeriod($sellerId, 'month'),
                'total' => $this->getEarningsForPeriod($sellerId, 'total')
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}