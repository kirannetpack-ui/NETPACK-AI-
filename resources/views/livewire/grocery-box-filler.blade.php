<div class="container mx-auto p-6">
    <div class="bg-gradient-to-r from-green-500 to-teal-500 rounded-lg shadow-lg p-6 mb-8 text-white">
        <h1 class="text-3xl font-bold">📦 Grocery Box Builder</h1>
        <p class="mt-2">Pack your products from Nepal - Ships worldwide!</p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Product Search Panel -->
        <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-4">
            <h2 class="text-xl font-bold mb-4">Add Products</h2>
            
            <div class="mb-4">
                <input type="text" 
                       wire:model.live="searchTerm" 
                       wire:keyup="searchProduct"
                       placeholder="Search Nepali products..." 
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            
            @if(count($searchResults) > 0)
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($searchResults as $product)
                        <div class="border rounded-lg p-3 hover:shadow-md cursor-pointer"
                             wire:click="addProductToBox({{ $product['id'] }})">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold">{{ $product['name'] }}</h3>
                                    <p class="text-sm text-gray-500">⚖️ {{ $product['weight'] }} kg</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-teal-600">रू {{ number_format($product['price'], 2) }}</p>
                                    <button class="mt-1 bg-teal-500 text-white px-3 py-1 rounded text-sm hover:bg-teal-600">
                                        Add +
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(strlen($searchTerm) > 1)
                <p class="text-gray-500 text-center py-4">No products found</p>
            @else
                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-600 mb-2">Popular Products:</h3>
                    @foreach(array_slice($products, 0, 3) as $product)
                        <div class="border rounded-lg p-3 hover:shadow-md cursor-pointer"
                             wire:click="addProductToBox({{ $product['id'] }})">
                            <div class="flex justify-between">
                                <span>{{ $product['name'] }}</span>
                                <span class="text-teal-600">{{ $product['weight'] }} kg</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Boxes Display Panel -->
        <div class="lg:col-span-2 bg-gray-50 rounded-lg shadow-md p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Your Boxes</h2>
                <button wire:click="optimizeBoxes" 
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    🔄 Optimize Packing
                </button>
            </div>
            
            <div class="space-y-4">
                @foreach($boxes as $boxIndex => $box)
                    <div class="bg-white rounded-lg border-2 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-700 to-gray-900 text-white px-4 py-2 flex justify-between">
                            <span class="font-bold">📦 Box {{ $box['number'] }}</span>
                            <span>{{ number_format($box['current_weight'], 1) }} kg / {{ $box['max_weight'] }} kg</span>
                        </div>
                        
                        <!-- Fill bar -->
                        <div class="h-2 bg-gray-200">
                            <div class="h-full bg-teal-500 transition-all duration-500" 
                                 style="width: {{ min($box['fill_percentage'], 100) }}%"></div>
                        </div>
                        
                        <div class="p-4">
                            @if(count($box['items']) > 0)
                                <table class="w-full text-sm">
                                    <thead class="border-b">
                                        <tr>
                                            <th class="text-left py-2">Product</th>
                                            <th class="text-center">Weight</th>
                                            <th class="text-right">Price</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($box['items'] as $itemIndex => $item)
                                            <tr class="border-b">
                                                <td class="py-2">{{ $item['name'] }}</td>
                                                <td class="text-center">{{ $item['weight'] }} kg</td>
                                                <td class="text-right">रू {{ number_format($item['price'], 2) }}</td>
                                                <td class="text-center">
                                                    <button wire:click="removeItem({{ $boxIndex }}, {{ $itemIndex }})"
                                                            class="text-red-500 hover:text-red-700 text-sm">
                                                        ❌
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-gray-500 text-center py-4">Empty box</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Totals -->
            <div class="mt-6 bg-gradient-to-r from-teal-500 to-green-500 rounded-lg p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Total Weight</p>
                        <p class="text-2xl font-bold">{{ number_format($totalWeight, 1) }} kg</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm opacity-90">Total Price</p>
                        <p class="text-2xl font-bold">रू {{ number_format($totalPrice, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm opacity-90">Boxes Used</p>
                        <p class="text-2xl font-bold">{{ count($boxes) }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-4 flex gap-3">
                <button wire:click="addNewBox" 
                        class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                    ➕ Add Empty Box
                </button>
                <button class="flex-1 bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700">
                    🚀 Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('product-added', () => {
            // Play sound or show notification
            console.log('Product added to box');
        });
        
        Livewire.on('boxes-optimized', () => {
            // Show success message
            alert('Boxes have been optimized for best packing!');
        });
    });
</script>
@endpush