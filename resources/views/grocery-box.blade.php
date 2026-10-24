{{-- resources/views/grocery-box.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Box Builder - COURIER with NETPACK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .box-transition {
            transition: all 0.3s ease;
        }
        
        .fill-bar {
            transition: width 0.5s ease;
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .icon-glow:hover {
            filter: drop-shadow(0 0 5px rgba(0, 210, 182, 0.5));
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }
    </style>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-7xl" id="app">
        <!-- Header with Stats -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl shadow-xl p-6 mb-8 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-box-open text-3xl"></i>
                        <h1 class="text-3xl font-bold">Grocery Box Builder</h1>
                    </div>
                    <p class="text-emerald-100">Pack authentic Nepali products - Ship worldwide</p>
                </div>
                <div class="text-right">
                    <div class="flex items-center gap-4">
                        <div>
                            <i class="fas fa-weight-hanging text-2xl mb-1"></i>
                            <p class="text-sm opacity-90">Max per box</p>
                            <p class="font-bold text-xl">{{ $maxBoxWeight ?? 30 }} kg</p>
                        </div>
                        <div class="h-12 w-px bg-emerald-400"></div>
                        <div>
                            <i class="fas fa-globe-asia text-2xl mb-1"></i>
                            <p class="text-sm opacity-90">Shipping to</p>
                            <p class="font-bold text-xl">Worldwide</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Products Panel -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden sticky top-8">
                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-5 py-4">
                        <h2 class="text-white font-bold text-lg flex items-center gap-2">
                            <i class="fas fa-store"></i>
                            <span>Products Catalog</span>
                        </h2>
                    </div>
                    
                    <div class="p-5">
                        <div class="relative mb-5">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" 
                                   id="search" 
                                   placeholder="Search products..." 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                        </div>
                        
                        <div id="products-list" class="space-y-3 max-h-[600px] overflow-y-auto pr-2">
                            @foreach($products as $product)
                            <div class="product-card bg-white border border-gray-200 rounded-xl p-4 cursor-pointer transition-all duration-200 hover:shadow-md"
                                 data-name="{{ strtolower($product['name']) }}"
                                 data-product='@json($product)'>
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        @if(isset($product['image']))
                                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-16 h-16 rounded-lg object-cover">
                                        @else
                                            <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-box-open text-2xl text-teal-600"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-800">{{ $product['name'] }}</h3>
                                        <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                            <span><i class="fas fa-weight-hanging"></i> {{ $product['weight'] }} kg</span>
                                            @if(isset($product['origin']))
                                                <span><i class="fas fa-map-marker-alt"></i> {{ $product['origin'] }}</span>
                                            @endif
                                        </div>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="font-bold text-teal-600">रू {{ number_format($product['price'], 2) }}</span>
                                            <button class="add-btn bg-teal-500 hover:bg-teal-600 text-white px-3 py-1 rounded-lg text-sm transition flex items-center gap-1">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Boxes Panel -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-5 py-4 flex justify-between items-center">
                        <h2 class="text-white font-bold text-lg flex items-center gap-2">
                            <i class="fas fa-boxes"></i>
                            <span>Your Packing Boxes</span>
                        </h2>
                        <div class="flex gap-2">
                            <button onclick="optimizeBoxes()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm transition flex items-center gap-1">
                                <i class="fas fa-magic"></i> Optimize
                            </button>
                            <button onclick="clearAllBoxes()" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-sm transition flex items-center gap-1">
                                <i class="fas fa-trash-alt"></i> Clear
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div id="boxes-container" class="space-y-4"></div>
                        
                        <!-- Totals and Checkout -->
                        <div id="totals" class="mt-6 bg-gradient-to-r from-teal-500 to-emerald-500 rounded-xl p-5 text-white"></div>
                        
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <button onclick="getPriceQuote()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-xl transition font-semibold flex items-center justify-center gap-2">
                                <i class="fas fa-file-invoice-dollar"></i> Get Quote
                            </button>
                            <button onclick="proceedToCheckout()" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-3 rounded-xl transition font-semibold flex items-center justify-center gap-2">
                                <i class="fas fa-arrow-right"></i> Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quote Modal -->
    <div id="quote-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 animate-slide-in">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-calculator text-teal-600"></i>
                    <span>Shipping Quote</span>
                </h3>
                <button onclick="closeQuote()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="quote-content"></div>
        </div>
    </div>
    
    <script>
        let boxes = [[]];
        let maxBoxWeight = {{ $maxBoxWeight ?? 30 }};
        let exchangeRate = {{ $exchangeRate ?? 133.5 }};
        const allProducts = @json($products);
        
        function addToBox(product) {
            let added = false;
            
            for (let i = 0; i < boxes.length; i++) {
                let boxWeight = boxes[i].reduce((sum, item) => sum + item.weight, 0);
                if (boxWeight + product.weight <= maxBoxWeight) {
                    boxes[i].push(product);
                    added = true;
                    break;
                }
            }
            
            if (!added) {
                boxes.push([product]);
            }
            
            renderBoxes();
            showToast(`${product.name} added to box`, 'success');
        }
        
        function removeFromBox(boxIndex, itemIndex) {
            const product = boxes[boxIndex][itemIndex];
            boxes[boxIndex].splice(itemIndex, 1);
            boxes = boxes.filter(box => box.length > 0);
            if (boxes.length === 0) boxes = [[]];
            renderBoxes();
            showToast(`${product.name} removed`, 'info');
        }
        
        function optimizeBoxes() {
            let allItems = [];
            boxes.forEach(box => allItems = allItems.concat(box));
            
            if (allItems.length === 0) {
                showToast('No items to optimize', 'warning');
                return;
            }
            
            allItems.sort((a, b) => b.weight - a.weight);
            boxes = [[]];
            
            allItems.forEach(item => {
                let added = false;
                for (let i = 0; i < boxes.length; i++) {
                    let boxWeight = boxes[i].reduce((sum, itm) => sum + itm.weight, 0);
                    if (boxWeight + item.weight <= maxBoxWeight) {
                        boxes[i].push(item);
                        added = true;
                        break;
                    }
                }
                if (!added) boxes.push([item]);
            });
            
            renderBoxes();
            showToast('Boxes optimized!', 'success');
        }
        
        function clearAllBoxes() {
            if (confirm('Clear all boxes?')) {
                boxes = [[]];
                renderBoxes();
                showToast('All boxes cleared', 'info');
            }
        }
        
        function getPriceQuote() {
            const totalWeight = calculateTotalWeight();
            const boxCount = boxes.filter(b => b.length > 0).length;
            let shippingCost = totalWeight > 0 ? 25 + (totalWeight * 15) : 0;
            
            document.getElementById('quote-content').innerHTML = `
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b">
                        <span><i class="fas fa-weight-hanging text-teal-600"></i> Total Weight:</span>
                        <span class="font-bold">${totalWeight.toFixed(2)} kg</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span><i class="fas fa-boxes text-teal-600"></i> Boxes:</span>
                        <span class="font-bold">${boxCount}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span><i class="fas fa-tag text-teal-600"></i> Products:</span>
                        <span class="font-bold">रू ${calculateTotalPrice().toLocaleString()}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span><i class="fas fa-shipping-fast text-teal-600"></i> Shipping:</span>
                        <span class="font-bold">रू ${(shippingCost * exchangeRate).toLocaleString()}</span>
                    </div>
                    <div class="flex justify-between pt-3 mt-2 border-t-2 border-teal-600">
                        <span class="font-bold text-lg">Total:</span>
                        <span class="font-bold text-xl text-teal-600">रू ${(calculateTotalPrice() + (shippingCost * exchangeRate)).toLocaleString()}</span>
                    </div>
                </div>
            `;
            document.getElementById('quote-modal').classList.remove('hidden');
            document.getElementById('quote-modal').classList.add('flex');
        }
        
        function proceedToCheckout() {
            if (calculateTotalWeight() === 0) {
                showToast('Add products first', 'warning');
                return;
            }
            localStorage.setItem('grocery_order', JSON.stringify({boxes: boxes}));
            window.location.href = '/checkout';
        }
        
        function calculateTotalWeight() {
            return boxes.reduce((sum, box) => sum + box.reduce((s, i) => s + i.weight, 0), 0);
        }
        
        function calculateTotalPrice() {
            return boxes.reduce((sum, box) => sum + box.reduce((s, i) => s + i.price, 0), 0);
        }
        
        function showToast(message, type) {
            // Simple toast - can be enhanced
            console.log(`[${type}] ${message}`);
        }
        
        function closeQuote() {
            document.getElementById('quote-modal').classList.add('hidden');
            document.getElementById('quote-modal').classList.remove('flex');
        }
        
        function renderBoxes() {
            const container = document.getElementById('boxes-container');
            container.innerHTML = '';
            const activeBoxes = boxes.filter(b => b.length > 0);
            
            if (activeBoxes.length === 0) {
                container.innerHTML = `<div class="text-center py-12 text-gray-400"><i class="fas fa-box-open text-5xl mb-3"></i><p>Your boxes are empty</p></div>`;
            } else {
                activeBoxes.forEach((box, idx) => {
                    const weight = box.reduce((s, i) => s + i.weight, 0);
                    const percent = (weight / maxBoxWeight) * 100;
                    container.innerHTML += `
                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 flex justify-between items-center">
                                <span class="font-bold"><i class="fas fa-box text-teal-600 mr-2"></i>Box ${idx + 1}</span>
                                <span class="text-sm">${weight.toFixed(1)} / ${maxBoxWeight} kg</span>
                            </div>
                            <div class="h-2 bg-gray-200"><div class="fill-bar h-full bg-teal-500" style="width: ${Math.min(percent, 100)}%"></div></div>
                            <div class="p-3">
                                ${box.map((item, i) => `
                                    <div class="flex justify-between items-center py-2 border-b last:border-0">
                                        <div class="flex items-center gap-2">
                                            ${item.image ? `<img src="${item.image}" class="w-8 h-8 rounded object-cover">` : `<i class="fas fa-box text-gray-400"></i>`}
                                            <span class="text-sm">${item.name}</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm">${item.weight} kg</span>
                                            <button onclick="removeFromBox(${idx}, ${i})" class="text-red-500 hover:text-red-700"><i class="fas fa-trash-alt text-sm"></i></button>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                });
            }
            
            document.getElementById('totals').innerHTML = `
                <div class="flex justify-between items-center">
                    <div><i class="fas fa-weight-hanging"></i> Total: ${calculateTotalWeight().toFixed(2)} kg</div>
                    <div><i class="fas fa-boxes"></i> Boxes: ${activeBoxes.length}</div>
                    <div class="text-right"><i class="fas fa-rupee-sign"></i> ${calculateTotalPrice().toLocaleString()}</div>
                </div>
            `;
        }
        
        // Event listeners
        document.querySelectorAll('.add-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const product = JSON.parse(btn.closest('.product-card').getAttribute('data-product'));
                addToBox(product);
            });
        });
        
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.classList.contains('add-btn') && !e.target.closest('.add-btn')) {
                    const product = JSON.parse(card.getAttribute('data-product'));
                    addToBox(product);
                }
            });
        });
        
        document.getElementById('search').addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                card.style.display = card.getAttribute('data-name').includes(term) ? 'flex' : 'none';
            });
        });
        
        renderBoxes();
    </script>
</body>
</html>
