<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $rider = Auth::user();
        
        $query = Delivery::where('rider_id', $rider->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $deliveries = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('rider.history', compact('deliveries'));
    }

    public function show($id)
    {
        $delivery = Delivery::where('rider_id', Auth::id())->findOrFail($id);
        return view('rider.delivery-details', compact('delivery'));
    }
}