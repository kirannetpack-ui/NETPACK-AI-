<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of seller's products.
     */
    public function index(Request $request)
    {
        $sellerId = Auth::id();
        
        $query = Product::where('user_id', $sellerId);
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $products = $query->orderBy('created_at', 'desc')->paginate(12);
        
        return view('seller.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('seller.products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $sellerId = Auth::id();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'sku' => 'required|string|max:100|unique:products,sku',
            'price_npr' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/' . $sellerId, 'public');
                $imagePaths[] = $path;
            }
        }

        $product = Product::create([
            'user_id' => $sellerId,
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'sku' => $request->sku,
            'price_npr' => $request->price_npr,
            'stock_quantity' => $request->stock_quantity,
            'images' => $imagePaths,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('seller.products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $sellerId = Auth::id();
        $product = Product::where('user_id', $sellerId)->findOrFail($id);
        
        return view('seller.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        $sellerId = Auth::id();
        $product = Product::where('user_id', $sellerId)->findOrFail($id);
        
        return view('seller.products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        $sellerId = Auth::id();
        $product = Product::where('user_id', $sellerId)->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'sku' => 'required|string|max:100|unique:products,sku,' . $id,
            'price_npr' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            // Delete old images
            if ($product->images) {
                foreach ($product->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
            
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/' . $sellerId, 'public');
                $imagePaths[] = $path;
            }
            $product->images = $imagePaths;
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'sku' => $request->sku,
            'price_npr' => $request->price_npr,
            'stock_quantity' => $request->stock_quantity,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('seller.products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Toggle product status (active/inactive).
     */
    public function toggleStatus($id)
    {
        $sellerId = Auth::id();
        $product = Product::where('user_id', $sellerId)->findOrFail($id);
        
        $product->update([
            'is_active' => !$product->is_active
        ]);
        
        $status = $product->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('seller.products.index')
            ->with('success', "Product {$status} successfully!");
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        $sellerId = Auth::id();
        $product = Product::where('user_id', $sellerId)->findOrFail($id);
        
        // Delete images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }
        
        $product->delete();
        
        return redirect()->route('seller.products.index')
            ->with('success', 'Product deleted successfully!');
    }
}