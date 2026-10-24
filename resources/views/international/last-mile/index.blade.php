@extends('layouts.app')

@section('title', 'Last Mile Delivery Companies | International Logistics')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 w-96 h-full opacity-10 pointer-events-none flex items-center justify-end pr-8">
            <i class="fas fa-truck text-9xl"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <i class="fas fa-shipping-fast mr-1"></i> Destination Courier Delivery
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        Canpar &bull; Obibox &bull; Royal Mail &bull; AusPost &bull; Local Couriers
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">Last Mile Delivery Companies</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Define and manage destination courier partners responsible for the final stage handover (e.g. Canpar / Obibox for Canada DDP in Toronto, Royal Mail for UK DDP, Australia Post for SYD loads).
                </p>
            </div>
            <div>
                <button onclick="document.getElementById('addCarrierModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> Add Delivery Company
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-lg"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-500 p-4 rounded-xl text-sm space-y-1">
            <p class="font-bold flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Errors occurred:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('international.last-mile.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search company name or code..." class="pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 w-64 focus:ring-2 focus:ring-indigo-500">
            </div>

            <select name="hub_id" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                <option value="">-- All Gateway Hubs --</option>
                @foreach($hubs as $hub)
                    <option value="{{ $hub->id }}" {{ request('hub_id') == $hub->id ? 'selected' : '' }}>{{ $hub->code }} - {{ $hub->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold">
                Filter
            </button>
            @if(request()->hasAny(['search', 'hub_id']))
                <a href="{{ route('international.last-mile.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700">Clear</a>
            @endif
        </form>
        <div class="text-xs text-slate-500">
            Total Carriers: <span class="font-bold text-slate-900 dark:text-white">{{ $carriers->total() }}</span>
        </div>
    </div>

    <!-- Carriers Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Carrier Company</th>
                        <th class="px-5 py-3.5">Code</th>
                        <th class="px-5 py-3.5">Associated Hub</th>
                        <th class="px-5 py-3.5">Country & Scope</th>
                        <th class="px-5 py-3.5">Tracking Link Template</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($carriers as $c)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-xs border border-emerald-200 dark:border-emerald-800">
                                    <i class="fas fa-truck-moving"></i>
                                </span>
                                <div>
                                    <span>{{ $c->name }}</span>
                                    <p class="text-[10px] text-slate-400 font-normal">{{ $c->service_mode ?? 'Direct Delivery' }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $c->code }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($c->hub)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                        {{ $c->hub->code }} ({{ $c->hub->name }})
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Global / Flexible</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <i class="fas fa-map-pin text-rose-500 mr-1"></i> {{ $c->country ?? 'Worldwide' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-[11px] text-slate-500 max-w-xs truncate">
                                {{ $c->tracking_url_template ?? 'N/A' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $c->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $c->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <button onclick="editCarrier({{ json_encode($c) }})" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Company">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('international.last-mile.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Delete carrier {{ $c->name }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                No last mile carriers found. Click "Add Delivery Company" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $carriers->links() }}
        </div>
    </div>
</div>

<!-- Modal: Add Carrier -->
<div id="addCarrierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-xl w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-truck text-emerald-500"></i> Add Last Mile Courier Company
            </h3>
            <button onclick="document.getElementById('addCarrierModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form action="{{ route('international.last-mile.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Canpar Express, Obibox, Royal Mail" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Unique Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" required placeholder="e.g. CANPAR, OBIBOX, ROYALMAIL" class="w-full uppercase font-mono text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Associated Hub</label>
                    <select name="hub_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        <option value="">-- All Hubs / Global --</option>
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}">{{ $hub->code }} - {{ $hub->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Country Coverage</label>
                    <input type="text" name="country" placeholder="e.g. Canada, United Kingdom, Australia" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Service Mode Description</label>
                <input type="text" name="service_mode" placeholder="e.g. Canada DDP Ground, UK 24hr Tracked, AusPost eParcel" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Public Tracking URL Template</label>
                <input type="text" name="tracking_url_template" placeholder="https://www.canpar.com/en/track/tracking.jsp?track_number={tracking_number}" class="w-full font-mono text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <p class="text-[10px] text-slate-400 mt-1">Use <code class="text-indigo-500 font-bold">{tracking_number}</code> as placeholder for direct client tracking redirect.</p>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="m_is_active" value="1" checked class="w-4 h-4 text-emerald-600 rounded">
                <label for="m_is_active" class="text-xs font-medium text-slate-700 dark:text-slate-300">Carrier is active and selectable</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addCarrierModal').classList.add('hidden')" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md">Add Carrier</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Carrier -->
<div id="editCarrierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-xl w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-edit text-indigo-500"></i> Edit Last Mile Carrier
            </h3>
            <button onclick="document.getElementById('editCarrierModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="editCarrierForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit_name" name="name" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Unique Code <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit_code" name="code" required class="w-full uppercase font-mono text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Associated Hub</label>
                    <select id="edit_hub_id" name="hub_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        <option value="">-- All Hubs / Global --</option>
                        @foreach($hubs as $hub)
                            <option value="{{ $hub->id }}">{{ $hub->code }} - {{ $hub->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Country Coverage</label>
                    <input type="text" id="edit_country" name="country" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Service Mode Description</label>
                <input type="text" id="edit_service_mode" name="service_mode" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Public Tracking URL Template</label>
                <input type="text" id="edit_tracking_url_template" name="tracking_url_template" class="w-full font-mono text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="w-4 h-4 text-indigo-600 rounded">
                <label for="edit_is_active" class="text-xs font-medium text-slate-700 dark:text-slate-300">Carrier is active</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('editCarrierModal').classList.add('hidden')" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-md">Update Carrier</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCarrier(carrier) {
    const form = document.getElementById('editCarrierForm');
    form.action = `/international/last-mile-carriers/${carrier.id}`;
    document.getElementById('edit_name').value = carrier.name || '';
    document.getElementById('edit_code').value = carrier.code || '';
    document.getElementById('edit_hub_id').value = carrier.hub_id || '';
    document.getElementById('edit_country').value = carrier.country || '';
    document.getElementById('edit_service_mode').value = carrier.service_mode || '';
    document.getElementById('edit_tracking_url_template').value = carrier.tracking_url_template || '';
    document.getElementById('edit_is_active').checked = !!carrier.is_active;

    document.getElementById('editCarrierModal').classList.remove('hidden');
}
</script>
@endsection
