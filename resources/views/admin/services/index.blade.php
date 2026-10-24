@extends('layouts.app')

@section('title', 'Dynamic Services & Transit Time SLA Management - COURIER with NETPACK')
@section('page-title', 'Services & Transit Time SLA')

@section('content')
<div class="space-y-6" x-data="{ 
    showAddModal: false, 
    showEditModal: false,
    editService: {
        id: '',
        name: '',
        code: '',
        category: 'domestic',
        transit_time_hours: 24,
        transit_time_days: 1,
        reminder_intervals: '50, 75, 90',
        base_rate: 0,
        per_kg_rate: 0,
        description: '',
        is_active: true
    },
    openEdit(service) {
        this.editService = {
            id: service.id,
            name: service.name,
            code: service.code,
            category: service.category,
            transit_time_hours: service.transit_time_hours,
            transit_time_days: service.transit_time_days || (service.transit_time_hours / 24).toFixed(2),
            reminder_intervals: Array.isArray(service.reminder_intervals) ? service.reminder_intervals.join(', ') : (service.reminder_intervals || '50, 75, 90'),
            base_rate: service.base_rate || 0,
            per_kg_rate: service.per_kg_rate || 0,
            description: service.description || '',
            is_active: Boolean(service.is_active)
        };
        this.showEditModal = true;
    }
}">

    <!-- Top Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-2xl p-6 text-white shadow-sm border border-teal-900/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        Dynamic SLA Engine
                    </span>
                    <span class="text-xs text-slate-400">&bull; International &bull; Domestic &bull; E-Commerce</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight">Services & Transit Time SLA Configurator</h1>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                    Dynamically manage logistics services across Domestic, International, and E-Commerce sectors. Every service determines package delivery deadlines and triggers automated delivery partner reminders at configured intervals.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="showAddModal = true" class="px-4 py-2 text-xs font-semibold bg-teal-500 hover:bg-teal-400 text-slate-950 rounded-lg transition shadow-md flex items-center gap-2 font-bold">
                    <i class="fas fa-plus"></i> Add New Service
                </button>
                <a href="{{ route('admin.communications') }}" class="px-3.5 py-2 text-xs font-semibold bg-white/10 hover:bg-white/20 text-white rounded-lg transition border border-white/10 flex items-center gap-2">
                    <i class="fas fa-bell"></i> Reminders Hub
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-circle-check text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600">&times;</button>
        </div>
    @endif

    @if($errors->any()))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-700 dark:text-rose-300 text-sm space-y-1">
            <div class="font-bold flex items-center gap-2">
                <i class="fas fa-circle-exclamation text-rose-500"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-2">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Category Tabs & Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- All Services Tab -->
        <a href="{{ route('admin.services.index', ['category' => 'all']) }}" 
           class="p-4 rounded-xl border transition {{ $category === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-md ring-2 ring-teal-500/30' : 'bg-white dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 hover:border-slate-300 text-slate-700 dark:text-slate-300' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Services</span>
                <i class="fas fa-layer-group text-teal-400"></i>
            </div>
            <div class="text-2xl font-black">{{ $categoryCounts['all'] ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">All Global & Domestic</div>
        </a>

        <!-- Domestic Services Tab -->
        <a href="{{ route('admin.services.index', ['category' => 'domestic']) }}" 
           class="p-4 rounded-xl border transition {{ $category === 'domestic' ? 'bg-teal-900 text-white border-teal-800 shadow-md ring-2 ring-teal-500/30' : 'bg-white dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 hover:border-slate-300 text-slate-700 dark:text-slate-300' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Domestic</span>
                <i class="fas fa-flag text-rose-400"></i>
            </div>
            <div class="text-2xl font-black text-teal-400">{{ $categoryCounts['domestic'] ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Valley Flash, Same-Day & Himalayan</div>
        </a>

        <!-- E-Commerce Tab -->
        <a href="{{ route('admin.services.index', ['category' => 'ecommerce']) }}" 
           class="p-4 rounded-xl border transition {{ $category === 'ecommerce' ? 'bg-amber-950 text-white border-amber-900 shadow-md ring-2 ring-amber-500/30' : 'bg-white dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 hover:border-slate-300 text-slate-700 dark:text-slate-300' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">E-Commerce</span>
                <i class="fas fa-store text-amber-400"></i>
            </div>
            <div class="text-2xl font-black text-amber-400">{{ $categoryCounts['ecommerce'] ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Merchant COD & Express Groceries</div>
        </a>

        <!-- International Tab -->
        <a href="{{ route('admin.services.index', ['category' => 'international']) }}" 
           class="p-4 rounded-xl border transition {{ $category === 'international' ? 'bg-blue-950 text-white border-blue-900 shadow-md ring-2 ring-blue-500/30' : 'bg-white dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 hover:border-slate-300 text-slate-700 dark:text-slate-300' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">International</span>
                <i class="fas fa-plane-departure text-cyan-400"></i>
            </div>
            <div class="text-2xl font-black text-cyan-400">{{ $categoryCounts['international'] ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Air Cargo & Global Freight</div>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row gap-4 items-center justify-between shadow-sm">
        <form action="{{ route('admin.services.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="hidden" name="category" value="{{ $category }}">
            
            <div class="relative flex-1 md:w-72">
                <i class="fas fa-search absolute left-3 top-2.5 text-xs text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search service name or code..." 
                       class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>

            <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.services.index', ['category' => $category]) }}" class="text-xs text-slate-500 hover:text-slate-700 underline">
                    Reset
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-500">
            Showing <span class="font-bold text-slate-800 dark:text-slate-200">{{ $services->count() }}</span> of {{ $services->total() }} services
        </div>
    </div>

    <!-- Services Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800 font-bold">
                    <tr>
                        <th class="px-4 py-3.5">Service Details</th>
                        <th class="px-4 py-3.5">Sector</th>
                        <th class="px-4 py-3.5">Transit Time SLA</th>
                        <th class="px-4 py-3.5">Reminder Milestones</th>
                        <th class="px-4 py-3.5">Tariff Defaults</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($services as $svc)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                    {{ $svc->name }}
                                </div>
                                <div class="font-mono text-[11px] text-teal-600 dark:text-teal-400 mt-0.5">
                                    <code>{{ $svc->code }}</code>
                                </div>
                                @if($svc->description)
                                    <p class="text-[11px] text-slate-400 mt-1 max-w-xs line-clamp-1">{{ $svc->description }}</p>
                                @endif
                            </td>

                            <td class="px-4 py-3.5">
                                @if($svc->category === 'domestic')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60">
                                        🇳🇵 Domestic
                                    </span>
                                @elseif($svc->category === 'ecommerce')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                        🛒 E-Commerce
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60">
                                        ✈️ International
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                    <i class="fas fa-clock text-amber-500"></i>
                                    <span>{{ $svc->transit_time_hours }} Hours</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    ≈ {{ $svc->transit_time_days ?: round($svc->transit_time_hours / 24, 2) }} Days
                                </div>
                            </td>

                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-1 items-center">
                                    @php
                                        $intervals = is_array($svc->reminder_intervals) ? $svc->reminder_intervals : [50, 75, 90];
                                    @endphp
                                    @foreach($intervals as $pct)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                                            {{ $pct }}%
                                        </span>
                                    @endforeach
                                </div>
                                <span class="text-[10px] text-slate-400 mt-0.5 block">Partner reminder triggers</span>
                            </td>

                            <td class="px-4 py-3.5">
                                <div class="text-xs font-mono">
                                    <span class="text-slate-500">Base:</span> 
                                    <span class="font-bold text-slate-800 dark:text-slate-200">NPR {{ number_format($svc->base_rate, 2) }}</span>
                                </div>
                                <div class="text-[10px] font-mono text-slate-400">
                                    +NPR {{ number_format($svc->per_kg_rate, 2) }}/kg
                                </div>
                            </td>

                            <td class="px-4 py-3.5">
                                <form action="{{ route('admin.services.toggle', $svc->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $svc->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-300 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-300 hover:bg-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $svc->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $svc->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>

                            <td class="px-4 py-3.5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button @click="openEdit({{ json_encode($svc) }})" 
                                            class="px-2.5 py-1 text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-teal-50 hover:text-teal-600 text-slate-700 dark:text-slate-300 rounded-lg transition border border-slate-200 dark:border-slate-700 flex items-center gap-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <form action="{{ route('admin.services.destroy', $svc->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete service {{ $svc->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 transition" title="Delete Service">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <i class="fas fa-cube text-3xl mb-2 text-slate-300"></i>
                                <p class="text-sm">No logistics services found matching this criteria.</p>
                                <button @click="showAddModal = true" class="mt-3 px-3.5 py-1.5 text-xs font-semibold bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                                    + Add First Service
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $services->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================= -->
    <!-- ADD SERVICE MODAL -->
    <!-- ============================================================= -->
    <div x-show="showAddModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAddModal = false" 
             class="bg-white dark:bg-slate-900 rounded-2xl max-w-xl w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-600 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-plus"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Add New Logistics Service</h3>
                        <p class="text-xs text-slate-400">Configure transit time SLA and partner reminder checkpoints</p>
                    </div>
                </div>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.services.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Service Display Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Flash 2-Hour Express" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Unique Service Code / Slug *</label>
                        <input type="text" name="code" required placeholder="e.g. flash_express_2h" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Logistics Category *</label>
                        <select name="category" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                            <option value="domestic" {{ $category === 'domestic' ? 'selected' : '' }}>🇳🇵 Domestic</option>
                            <option value="ecommerce" {{ $category === 'ecommerce' ? 'selected' : '' }}>🛒 E-Commerce</option>
                            <option value="international" {{ $category === 'international' ? 'selected' : '' }}>✈️ International</option>
                        </select>
                    </div>

                    <!-- Transit Time Hours -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Transit Time SLA (Hours) *</label>
                        <input type="number" step="0.25" min="0.25" max="2160" name="transit_time_hours" required placeholder="e.g. 4 or 72" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Delivery deadline = Booking Time + Transit Hours</span>
                    </div>

                    <!-- Reminder Intervals -->
                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Partner Reminder Intervals (% of SLA Elapsed)
                        </label>
                        <input type="text" name="reminder_intervals" value="50, 75, 90" placeholder="e.g. 50, 75, 90" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Comma-separated percentages. For 4h SLA, 50% = 2h mark, 75% = 3h mark.</span>
                    </div>

                    <!-- Base Rate -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Base Rate (NPR)</label>
                        <input type="number" step="0.01" min="0" name="base_rate" value="0" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Per KG Rate -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Per KG Increment (NPR)</label>
                        <input type="number" step="0.01" min="0" name="per_kg_rate" value="0" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Description / SLA Scope</label>
                        <textarea name="description" rows="2" placeholder="Brief scope of coverage, e.g. Intra-Kathmandu valley wards only..." 
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>

                    <!-- Active Toggle -->
                    <div class="md:col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="add_is_active" value="1" checked class="rounded text-teal-600 focus:ring-teal-500">
                        <label for="add_is_active" class="text-xs text-slate-700 dark:text-slate-300 font-medium">Activate service immediately for new bookings</label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 font-semibold transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold transition shadow-md">
                        Save Service
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- EDIT SERVICE MODAL -->
    <!-- ============================================================= -->
    <div x-show="showEditModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showEditModal = false" 
             class="bg-white dark:bg-slate-900 rounded-2xl max-w-xl w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-600 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-edit"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Edit Logistics Service</h3>
                        <p class="text-xs text-slate-400">Update transit time SLA and partner reminder rules</p>
                    </div>
                </div>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form :action="'{{ url('admin/services') }}/' + editService.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Service Display Name *</label>
                        <input type="text" name="name" x-model="editService.name" required 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Service Code / Slug *</label>
                        <input type="text" name="code" x-model="editService.code" required 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                        <select name="category" x-model="editService.category" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                            <option value="domestic">🇳🇵 Domestic</option>
                            <option value="ecommerce">🛒 E-Commerce</option>
                            <option value="international">✈️ International</option>
                        </select>
                    </div>

                    <!-- Transit Time Hours -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Transit Time SLA (Hours) *</label>
                        <input type="number" step="0.25" min="0.25" max="2160" name="transit_time_hours" x-model="editService.transit_time_hours" required 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Reminder Intervals -->
                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Partner Reminder Intervals (% Milestones)
                        </label>
                        <input type="text" name="reminder_intervals" x-model="editService.reminder_intervals" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Delivery partners receive alerts at these SLA percentage marks.</span>
                    </div>

                    <!-- Base Rate -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Base Rate (NPR)</label>
                        <input type="number" step="0.01" min="0" name="base_rate" x-model="editService.base_rate" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Per KG Rate -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Per KG Rate (NPR)</label>
                        <input type="number" step="0.01" min="0" name="per_kg_rate" x-model="editService.per_kg_rate" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                        <textarea name="description" x-model="editService.description" rows="2" 
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>

                    <!-- Active Toggle -->
                    <div class="md:col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-model="editService.is_active" class="rounded text-teal-600 focus:ring-teal-500">
                        <label for="edit_is_active" class="text-xs text-slate-700 dark:text-slate-300 font-medium">Service is active</label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 font-semibold transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold transition shadow-md">
                        Update Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
