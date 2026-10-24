<!-- MANIFESTS -->
<div class="px-3 mt-6">
    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">MANIFESTS</div>
</div>

<!-- All Manifests -->
<a href="{{ route('domestic.manifests.index') }}" 
   class="flex items-center px-4 py-3 text-sm rounded-lg transition 
          {{ request()->routeIs('domestic.manifests.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
    <i class="fas fa-boxes w-5 {{ request()->routeIs('domestic.manifests.index') ? 'text-blue-600' : 'text-gray-500' }}"></i>
    <span class="ml-3">All Manifests</span>
    <span class="ml-auto bg-blue-600 text-xs text-white px-2 py-1 rounded-full">{{ App\Models\Manifest::count() }}</span>
</a>

<!-- Create Manifest -->
@if(Route::has('domestic.manifests.create'))
<a href="{{ route('domestic.manifests.create') }}" 
   class="flex items-center px-4 py-3 text-sm rounded-lg transition 
          {{ request()->routeIs('domestic.manifests.create') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
    <i class="fas fa-plus-circle w-5 {{ request()->routeIs('domestic.manifests.create') ? 'text-blue-600' : 'text-green-500' }}"></i>
    <span class="ml-3">Create Manifest</span>
</a>
@endif

<!-- Proof of Delivery -->
<a href="{{ route('domestic.manifests.pods') }}" 
   class="flex items-center px-4 py-3 text-sm rounded-lg transition 
          {{ request()->routeIs('domestic.manifests.pods*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
    <i class="fas fa-file-signature w-5 {{ request()->routeIs('domestic.manifests.pods*') ? 'text-blue-600' : 'text-purple-500' }}"></i>
    <span class="ml-3">Proof of Delivery</span>
    <span class="ml-auto bg-green-600 text-xs text-white px-2 py-1 rounded-full">{{ App\Models\ProofOfDelivery::count() }}</span>
</a>