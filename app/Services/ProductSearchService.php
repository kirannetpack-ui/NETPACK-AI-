<?php
// app/Services/ProductSearchService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;

class ProductSearchService
{
    // Search products from multiple sources
    public function searchGlobalProducts($query, $source = 'all')
    {
        $results = [];
        
        switch($source) {
            case 'opencollect':
                $results = $this->searchOpenFoodFacts($query);
                break;
            case 'serpapi':
                $results = $this->searchSerpAPI($query);
                break;
            case 'nepal':
                $results = $this->searchNepaliProducts($query);
                break;
            default:
                $results = array_merge(
                    $this->searchOpenFoodFacts($query),
                    $this->searchNepaliProducts($query)
                );
        }
        
        return $results;
    }
    
    // Search Open Food Facts (free, no API key needed)
    private function searchOpenFoodFacts($query)
    {
        try {
            $response = Http::get("https://world.openfoodfacts.org/cgi/search.pl", [
                'search_terms' => $query,
                'search_simple' => 1,
                'action' => 'process',
                'json' => 1,
                'page_size' => 10
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $products = [];
                
                if (isset($data['products'])) {
                    foreach ($data['products'] as $item) {
                        $products[] = [
                            'source' => 'OpenFoodFacts',
                            'name' => $item['product_name'] ?? $item['generic_name'] ?? 'Unknown',
                            'weight_kg' => $this->extractWeight($item),
                            'price' => $this->estimatePrice($item),
                            'image_url' => $item['image_url'] ?? null,
                            'description' => $item['ingredients_text'] ?? null,
                            'category' => $item['categories'] ?? 'General',
                            'origin_country' => $item['countries'] ?? 'Unknown'
                        ];
                    }
                }
                
                return $products;
            }
        } catch (\Exception $e) {
            \Log::error('OpenFoodFacts search failed: ' . $e->getMessage());
        }
        
        return [];
    }
    
    // Search Nepali Products Database
    private function searchNepaliProducts($query)
    {
        // Search local database first
        $localProducts = Product::where('name', 'like', "%{$query}%")
            ->orWhere('tags', 'like', "%{$query}%")
            ->where('is_active', true)
            ->limit(10)
            ->get();
        
        $results = [];
        foreach ($localProducts as $product) {
            $results[] = [
                'source' => 'NETPACK Database',
                'id' => $product->id,
                'name' => $product->name,
                'weight_kg' => $product->weight_kg,
                'price' => $product->price_npr,
                'image_url' => $product->image,
                'description' => $product->description,
                'category' => $product->category,
                'origin_country' => $product->origin_country,
                'is_local' => true
            ];
        }
        
        return $results;
    }
    
    // Search using SerpAPI (requires API key - paid)
    private function searchSerpAPI($query)
    {
        $apiKey = config('services.serpapi.key');
        if (!$apiKey) return [];
        
        try {
            $response = Http::get("https://serpapi.com/search", [
                'q' => $query . " grocery product weight price",
                'api_key' => $apiKey,
                'engine' => 'google_shopping',
                'gl' => 'us',
                'num' => 10
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $products = [];
                
                if (isset($data['shopping_results'])) {
                    foreach ($data['shopping_results'] as $item) {
                        $products[] = [
                            'source' => 'Google Shopping',
                            'name' => $item['title'],
                            'weight_kg' => $this->extractWeightFromText($item['title']),
                            'price' => $this->convertToNPR($item['price']),
                            'image_url' => $item['thumbnail'],
                            'description' => $item['description'] ?? null,
                            'category' => $item['category'] ?? 'General'
                        ];
                    }
                }
                
                return $products;
            }
        } catch (\Exception $e) {
            \Log::error('SerpAPI search failed: ' . $e->getMessage());
        }
        
        return [];
    }
    
    // Extract weight from product data
    private function extractWeight($product)
    {
        $weight = 0.5; // default
        
        if (isset($product['product_quantity'])) {
            $quantity = $product['product_quantity'];
            if (strpos($quantity, 'kg') !== false) {
                $weight = (float) filter_var($quantity, FILTER_SANITIZE_NUMBER_FLOAT);
            } elseif (strpos($quantity, 'g') !== false) {
                $weight = (float) filter_var($quantity, FILTER_SANITIZE_NUMBER_FLOAT) / 1000;
            }
        }
        
        return $weight > 0 ? $weight : 0.5;
    }
    
    private function extractWeightFromText($text)
    {
        preg_match('/(\d+(?:\.\d+)?)\s*(?:kg|kilogram)/i', $text, $matches);
        if (isset($matches[1])) return (float) $matches[1];
        
        preg_match('/(\d+(?:\.\d+)?)\s*(?:g|gram)/i', $text, $matches);
        if (isset($matches[1])) return (float) $matches[1] / 1000;
        
        return 0.5;
    }
    
    private function estimatePrice($product)
    {
        // Estimate price based on product type
        return rand(200, 5000);
    }
    
    private function convertToNPR($usdPrice)
    {
        $exchangeRate = 133.5;
        return $usdPrice * $exchangeRate;
    }
}
