<?php
// app/Http/Controllers/CheckoutController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout');
    }
    
    public function process(Request $request)
    {
        // Process order and create shipment
        // Generate tracking number
        // Create HAWB
        // Redirect to payment
        
        return redirect()->route('payment')->with('success', 'Order placed successfully!');
    }
}