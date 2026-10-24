@extends('layouts.app')

@section('title', 'Domestic Partner Assignments')
@section('page-title', 'Domestic Partner Assignments')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold text-slate-900">Territory partner routing</h1>
        <p class="mt-1 text-sm text-slate-500">Set one default partner and any number of approved alternatives for each territory, service and delivery leg.</p>
        @if(session('success'))<div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form method="POST" action="{{ route('admin.domestic.assignments.store') }}" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-4">@csrf
            <select name="zone_id" required class="rounded-lg border-slate-300"><option value="">Territory</option>@foreach($zones as $zone)<option value="{{ $zone->id }}" @selected(old('zone_id') == $zone->id)>{{ $zone->zone_name }}</option>@endforeach</select>
            <select name="partner_id" required class="rounded-lg border-slate-300"><option value="">Partner</option>@foreach($partners as $partner)<option value="{{ $partner->id }}" @selected(old('partner_id') == $partner->id)>{{ $partner->name }}</option>@endforeach</select>
            <select name="leg_type" required class="rounded-lg border-slate-300"><option value="">Delivery leg</option>@foreach(['pickup'=>'Pickup','logistics'=>'Inter-zone logistics','delivery'=>'Last-mile delivery','door_to_door'=>'Door to door'] as $code=>$label)<option value="{{ $code }}" @selected(old('leg_type')===$code)>{{ $label }}</option>@endforeach</select>
            <select name="service_type" required class="rounded-lg border-slate-300">@foreach($services as $code=>$label)<option value="{{ $code }}" @selected(old('service_type','standard')===$code)>{{ $label }}</option>@endforeach</select>
            <input type="number" name="priority" value="{{ old('priority', 1) }}" min="1" max="999" required placeholder="Priority" class="rounded-lg border-slate-300">
            <input type="number" name="daily_capacity" value="{{ old('daily_capacity') }}" min="1" placeholder="Daily capacity (optional)" class="rounded-lg border-slate-300">
            <input type="time" name="cutoff_time" value="{{ old('cutoff_time') }}" class="rounded-lg border-slate-300">
            <div class="flex items-center gap-5"><label class="text-sm"><input type="checkbox" name="is_default" value="1" @checked(old('is_default'))> Default</label><label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Active</label></div>
            <button class="rounded-lg bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700 md:col-span-4">Save assignment</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm"><thead class="bg-slate-50 text-left text-slate-600"><tr><th class="p-3">Territory</th><th class="p-3">Partner</th><th class="p-3">Leg</th><th class="p-3">Service</th><th class="p-3">Routing</th><th class="p-3">Capacity</th><th class="p-3">Action</th></tr></thead>
        <tbody>@forelse($assignments as $assignment)<tr class="border-t"><td class="p-3">{{ $assignment->zone?->zone_name }}</td><td class="p-3 font-medium">{{ $assignment->partner?->name }}</td><td class="p-3">{{ ucfirst(str_replace('_',' ',$assignment->leg_type)) }}</td><td class="p-3">{{ strtoupper(str_replace('_',' ',$assignment->service_type)) }}</td><td class="p-3"><span class="rounded-full px-2 py-1 text-xs {{ $assignment->is_default ? 'bg-teal-100 text-teal-800' : 'bg-blue-100 text-blue-800' }}">{{ $assignment->is_default ? 'Default' : 'Alternative #'.$assignment->priority }}</span> @if(!$assignment->is_active)<span class="text-xs text-red-700">Inactive</span>@endif</td><td class="p-3">{{ $assignment->daily_capacity ?: 'Not limited' }}</td><td class="p-3"><form method="POST" action="{{ route('admin.domestic.assignments.destroy',$assignment) }}" onsubmit="return confirm('Remove this assignment?')">@csrf @method('DELETE')<button class="text-red-700">Remove</button></form></td></tr>@empty<tr><td colspan="7" class="p-8 text-center text-slate-500">No partner assignments have been configured.</td></tr>@endforelse</tbody></table>
        <div class="p-4">{{ $assignments->links() }}</div>
    </div>
</div>
@endsection
