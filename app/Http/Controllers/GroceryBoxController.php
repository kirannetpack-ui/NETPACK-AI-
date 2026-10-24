<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class GroceryBoxController extends Controller
{
    public function index()
    {
        // Load products from database
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'weight' => $product->weight_kg,
                    'price' => $product->price_npr,
                    'origin' => $product->origin_country,
                    'image' => $product->image_path ? asset('storage/' . $product->image_path) : null,
                    'sku' => $product->sku
                ];
            })
            ->toArray();
        
        $maxBoxWeight = 30; // kg per box
        $exchangeRate = 133.5; // USD to NPR
        
        return view('grocery-box', compact('products', 'maxBoxWeight', 'exchangeRate'));
    }
}