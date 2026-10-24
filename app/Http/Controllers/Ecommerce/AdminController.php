<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'sellers' => User::where('user_type', 'seller')->count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];
        
        return view('ecommerce.admin.dashboard', compact('stats'));
    }

    public function sellers()
    {
        $sellers = User::where('user_type', 'seller')->paginate(20);
        return view('ecommerce.admin.sellers', compact('sellers'));
    }

    public function products()
    {
        $products = Product::with('user')->paginate(20);
        return view('ecommerce.admin.products', compact('products'));
    }

    public function orders()
    {
        $orders = Order::with(['seller', 'client'])->paginate(20);
        return view('ecommerce.admin.orders', compact('orders'));
    }
}