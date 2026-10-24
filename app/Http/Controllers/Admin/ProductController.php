<?php
// app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $searchService;
    
    public function __construct(ProductSearchService $searchService)
    {
        $this->searchService = $searchService;
    }
    
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.products.index', compact('products'));
    }
    
    public function create()
    {
        return view('admin.products.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'description' => 'nullable|string',
            'weight_kg' => 'required|numeric|min:0.01',
            'length_cm' => 'nullable|numeric',
            'width_cm' => 'nullable|numeric',
            'height_cm' => 'nullable|numeric',
            'price_npr' => 'required|numeric|min:0',
            'price_usd' => 'nullable|numeric',
            'category' => 'required|string',
            'origin_country' => 'required|string',
            'origin_city' => 'nullable|string',
            'stock_quantity' => 'integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $path;
        }
        
        Product::create($validated);
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }
    
    public function searchGlobal(Request $request)
    {
        $request->validate(['query' => 'required|string|min:2']);
        
        $results = $this->searchService->searchGlobalProducts($request->query);
        
        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }
    
    public function import(Request $request, $id)
    {
        // Import product from search result
        $productData = $request->all();
        
        $product = Product::create([
            'name' => $productData['name'],
            'sku' => $this->generateSku($productData['name']),
            'description' => $productData['description'] ?? null,
            'weight_kg' => $productData['weight_kg'],
            'price_npr' => $productData['price'],
            'category' => $productData['category'] ?? 'General',
            'origin_country' => $productData['origin_country'] ?? 'Nepal',
            'image_url' => $productData['image_url'] ?? null,
            'is_active' => true
        ]);
        
        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }
    
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }
    
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight_kg' => 'required|numeric|min:0.01',
            'length_cm' => 'nullable|numeric',
            'width_cm' => 'nullable|numeric',
            'height_cm' => 'nullable|numeric',
            'price_npr' => 'required|numeric|min:0',
            'price_usd' => 'nullable|numeric',
            'category' => 'required|string',
            'origin_country' => 'required|string',
            'origin_city' => 'nullable|string',
            'stock_quantity' => 'integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $path;
        }
        
        $product->update($validated);
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    }
    
    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }
    
    private function generateSku($name)
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        $random = rand(1000, 9999);
        $sku = $prefix . '-' . $random;
        
        while (Product::where('sku', $sku)->exists()) {
            $random = rand(1000, 9999);
            $sku = $prefix . '-' . $random;
        }
        
        return $sku;
    }
}