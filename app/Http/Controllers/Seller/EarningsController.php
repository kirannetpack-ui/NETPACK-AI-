<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EarningsController extends Controller
{
    /**
     * Display earnings page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $sellerId = Auth::id();
        $wallet = Wallet::where('user_id', $sellerId)->first();
        
        // Get earnings by period
        $period = $request->period ?? 'month';
        
        $earnings = [
            'total' => $this->getEarnings($sellerId, 'total'),
            'month' => $this->getEarnings($sellerId, 'month'),
            'week' => $this->getEarnings($sellerId, 'week'),
            'today' => $this->getEarnings($sellerId, 'today'),
        ];
        
        // Get transactions
        $transactions = Transaction::whereHas('wallet', function($q) use ($sellerId) {
            $q->where('user_id', $sellerId);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(20);
        
        // Get chart data
        $chartData = $this->getChartData($sellerId);
        
        // Get earnings by source
        $earningsBySource = Transaction::whereHas('wallet', function($q) use ($sellerId) {
            $q->where('user_id', $sellerId);
        })
        ->where('type', 'credit')
        ->select('source', \DB::raw('SUM(amount) as total'))
        ->groupBy('source')
        ->get();
        
        return view('seller.earnings.index', compact(
            'wallet',
            'earnings',
            'transactions',
            'chartData',
            'earningsBySource',
            'period'
        ));
    }

    /**
     * Get earnings for a specific period.
     *
     * @param int $sellerId
     * @param string $period
     * @return array
     */
    private function getEarnings($sellerId, $period)
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
     * Get chart data for earnings.
     *
     * @param int $sellerId
     * @return array
     */
    private function getChartData($sellerId)
    {
        $months = [];
        $earnings = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');
            
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
     * Export earnings report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // Export earnings as CSV or Excel
        // This is a placeholder - implement as needed
        return redirect()->back()->with('info', 'Export feature coming soon!');
    }
}