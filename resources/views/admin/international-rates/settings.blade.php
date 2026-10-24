@extends('layouts.app')

@section('title', 'Dynamic Tariff Settings & Packaging Catalog - NETPACK Admin')
@section('page-title', 'Tariff & Packaging Dynamic Controls')

@section('content')
<script>
function tariffSettingsPage() {
    return {
        showAddModal: false,
        showEditModal: false,
        editItem: { id: '', code: '', name: '', price: 0, description: '', icon: 'box', sort_order: 0, is_active: true },
        packages: @json($packagingMaterials->keyBy('id')),

        openEdit(id) {
            const item = this.packages[id];
            if (item) {
                this.editItem = {
                    id: item.id,
                    code: item.code || '',
                    name: item.name || '',
                    price: parseFloat(item.price) || 0,
                    description: item.description || '',
                    icon: item.icon || 'box',
                    sort_order: parseInt(item.sort_order) || 0,
                    is_active: Boolean(item.is_active)
                };
                this.showEditModal = true;
            }
        },

        async toggleStatus(id) {
            try {
                const res = await fetch('{{ url('admin/international-rates/settings/packaging') }}/' + id + '/toggle', {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                }
            } catch (e) {
                console.error(e);
            }
        }
    };
}
</script>

<div class="space-y-6" x-data="tariffSettingsPage()">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 rounded-2xl p-6 text-white shadow-sm border border-slate-700/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                    Super Admin Dynamic Controls
                </span>
                <span class="text-xs text-slate-400">&bull; Live System Feeds</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white">Dynamic Tariff Settings & Packaging Catalog</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Feed baseline Customs Clearance and Godown terminal charges dynamically across all client rate inquiries, and manage dynamic packaging material tariffs.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('rates.inquiry') }}" target="_blank"
               class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-white/10">
                <i class="fas fa-calculator text-amber-400"></i>
                <span>Open Rate Inquiry Desk</span>
                <i class="fas fa-external-link-alt text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('admin.international-rates.index') }}" 
               class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Rate Matrices</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-xs flex items-center gap-2 shadow-2xs">
            <i class="fas fa-check-circle text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl text-xs flex items-center gap-2 shadow-2xs">
            <i class="fas fa-exclamation-circle text-rose-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-300 text-rose-800 p-4 rounded-xl text-xs">
            <div class="font-bold mb-1">Please correct the following errors:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 1. DYNAMIC CUSTOMS CLEARANCE & GODOWN CHARGES FEED PANEL -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-teal-50/40 via-white to-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-satellite-dish"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">Global Customs Clearance & Godown Charges (Live Dynamic Feed)</h2>
                    <p class="text-xs text-slate-500">
                        These baseline fees are automatically displayed to the client on every international rate quotation until manually updated.
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 bg-teal-100 text-teal-800 rounded-full text-[10px] font-bold uppercase tracking-wider self-start sm:self-auto">
                Live Broadcast Active
            </span>
        </div>

        <form method="POST" action="{{ route('admin.international-rates.settings.tariff') }}" class="p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customs Clearance Baseline Charge -->
                <div class="bg-slate-50/80 p-4 rounded-xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            <i class="fas fa-file-invoice text-teal-600 mr-1"></i> Default Customs Clearance Charge (NPR) <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[10px] bg-teal-50 text-teal-700 font-bold px-2 py-0.5 rounded border border-teal-200">Per Consignment</span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-bold">Rs.</span>
                        <input type="number" step="0.01" min="0" name="default_customs_clearance_charge" 
                               value="{{ old('default_customs_clearance_charge', $currentCustoms) }}" required
                               class="w-full text-sm font-mono font-bold pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-slate-900 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Customer Explanatory Note</label>
                        <input type="text" name="customs_charge_notice" 
                               value="{{ old('customs_charge_notice', $customsNotice) }}"
                               placeholder="e.g. Mandatory origin export customs inspection & clearance at TIA Cargo Customs."
                               class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-teal-500 text-slate-700">
                    </div>
                </div>

                <!-- Godown / Terminal Handling Baseline Charge -->
                <div class="bg-slate-50/80 p-4 rounded-xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            <i class="fas fa-warehouse text-teal-600 mr-1"></i> Default Airport Godown / Terminal Charge (NPR / KG) <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[10px] bg-teal-50 text-teal-700 font-bold px-2 py-0.5 rounded border border-teal-200">Per KG</span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-bold">Rs.</span>
                        <input type="number" step="0.01" min="0" name="default_godown_charge" 
                               value="{{ old('default_godown_charge', $currentGodown) }}" required
                               class="w-full text-sm font-mono font-bold pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-slate-900 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Customer Explanatory Note</label>
                        <input type="text" name="godown_charge_notice" 
                               value="{{ old('godown_charge_notice', $godownNotice) }}"
                               placeholder="e.g. TIA Air Cargo Terminal handling, weighing, screening, and godown staging fee."
                               class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg outline-none focus:ring-1 focus:ring-teal-500 text-slate-700">
                    </div>
                </div>
            </div>

            <!-- Optional Sync Checkbox -->
            <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl flex items-center justify-between">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="sync_all_matrices" value="1" class="w-4 h-4 text-teal-600 rounded border-slate-300 focus:ring-teal-500">
                    <span class="text-xs font-bold text-amber-900">
                        Synchronize & apply these charges across all {{ $totalRateMatrices }} existing international rate matrices in database
                    </span>
                </label>
                <span class="text-[10px] text-amber-700 font-medium hidden sm:inline">1-Click Bulk Propagation</span>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-xs transition flex items-center gap-2">
                    <i class="fas fa-floppy-disk"></i>
                    <span>Save & Feed Live Charges</span>
                </button>
            </div>
        </form>
    </div>

    <!-- 2. DYNAMIC PACKAGING MATERIALS MANAGEMENT -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">Packaging Materials & Dynamic Charges</h2>
                    <p class="text-xs text-slate-500">
                        Export packaging options and charges are loaded dynamically from this database catalog instead of being hard-coded.
                    </p>
                </div>
            </div>

            <button type="button" @click="showAddModal = true" 
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                <i class="fas fa-plus"></i>
                <span>Add Packaging Type</span>
            </button>
        </div>

        <!-- Packaging Materials Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/70 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Item & Code</th>
                        <th class="py-3 px-4">Dynamic Tariff (NPR)</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4 text-center">Sort Order</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($packagingMaterials as $pack)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center text-sm flex-shrink-0">
                                        <i class="fas fa-{{ $pack->icon ?: 'box' }}"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $pack->name }}</div>
                                        <code class="text-[10px] text-slate-400 bg-slate-100 px-1 py-0.5 rounded font-mono">{{ $pack->code }}</code>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono font-black text-sm {{ $pack->price > 0 ? 'text-teal-700' : 'text-slate-400' }}">
                                    {{ $pack->price > 0 ? 'Rs. ' . number_format($pack->price, 2) : 'FREE (Rs. 0)' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 max-w-xs text-slate-500 text-[11px] leading-relaxed">
                                {{ $pack->description ?: '—' }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-slate-600">
                                {{ $pack->sort_order }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button type="button" @click="toggleStatus({{ $pack->id }})"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition cursor-pointer {{ $pack->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                    {{ $pack->is_active ? 'Active' : 'Disabled' }}
                                </button>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openEdit({{ $pack->id }})" 
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition"
                                            title="Edit Packaging Details & Price">
                                        <i class="fas fa-pencil text-[11px]"></i>
                                    </button>

                                    @if($pack->code !== 'none')
                                        <form method="POST" action="{{ route('admin.international-rates.settings.packaging.destroy', $pack->id) }}" 
                                              onsubmit="return confirm('Are you sure you want to remove this packaging material?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition"
                                                    title="Delete Packaging Type">
                                                <i class="fas fa-trash text-[11px]"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                No dynamic packaging materials configured.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: ADD NEW PACKAGING MATERIAL -->
    <div x-show="showAddModal" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showAddModal = false" class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-box-open text-teal-600"></i>
                    <span>Add New Packaging Material</span>
                </h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.international-rates.settings.packaging.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Unique Code *</label>
                        <input type="text" name="code" placeholder="e.g. heavy_wooden_box" required
                               class="w-full text-xs font-mono px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Price (NPR) *</label>
                        <input type="number" step="0.01" min="0" name="price" placeholder="0.00" required
                               class="w-full text-xs font-mono font-bold px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Material Name *</label>
                    <input type="text" name="name" placeholder="e.g. Reinforced Thermocol Insulated Box" required
                           class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Customer Description</label>
                    <textarea name="description" rows="2" placeholder="Brief explanation of packaging safety and specifications..."
                              class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icon (FontAwesome)</label>
                        <input type="text" name="icon" placeholder="box, pallet, shield-halved" value="box"
                               class="w-full text-xs font-mono px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sort Order</label>
                        <input type="number" step="1" name="sort_order" value="10"
                               class="w-full text-xs font-mono px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-teal-600 rounded">
                        <span>Active Immediately</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold uppercase rounded-xl transition">
                            Save Material
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EDIT PACKAGING MATERIAL -->
    <div x-show="showEditModal" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showEditModal = false" class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-pencil text-teal-600"></i>
                    <span>Edit Packaging Material: <span class="font-mono text-teal-700" x-text="editItem.code"></span></span>
                </h3>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/international-rates/settings/packaging') }}/' + editItem.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Code</label>
                        <input type="text" :value="editItem.code" disabled
                               class="w-full text-xs font-mono px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Price (NPR) *</label>
                        <input type="number" step="0.01" min="0" name="price" x-model.number="editItem.price" required
                               class="w-full text-xs font-mono font-bold px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Material Name *</label>
                    <input type="text" name="name" x-model="editItem.name" required
                           class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Customer Description</label>
                    <textarea name="description" rows="2" x-model="editItem.description"
                              class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icon</label>
                        <input type="text" name="icon" x-model="editItem.icon"
                               class="w-full text-xs font-mono px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sort Order</label>
                        <input type="number" step="1" name="sort_order" x-model.number="editItem.sort_order"
                               class="w-full text-xs font-mono px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" :checked="editItem.is_active" class="w-4 h-4 text-teal-600 rounded">
                        <span>Active</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold uppercase rounded-xl transition">
                            Update Material
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
