<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shipment;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalShipments = Shipment::count();
        $pendingUsers = User::where('verification_status', 'pending')->count();
        $totalRevenue = 0; // Replace with actual revenue calculation
        
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        $recentShipments = Shipment::orderBy('created_at', 'desc')->take(5)->get();
        
        // Active moving shipments across the network (not delivered, cancelled, or returned)
        $activeShipments = Shipment::whereNotIn('status', ['delivered', 'cancelled', 'returned'])
            ->latest('updated_at')
            ->take(8)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalUsers',
            'totalShipments',
            'pendingUsers',
            'totalRevenue',
            'recentUsers',
            'recentShipments',
            'activeShipments'
        ));
    }
}