<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomesticRate;
use App\Models\DomesticRateEvent;
use App\Models\DeliveryZone;
use App\Models\LogisticsService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DomesticRateController extends Controller
{
    public function index(Request $request)
    {
        $query = DomesticRate::with(['partner', 'originZone', 'destinationZone'])
            ->when($request->filled('approval_status'), fn ($q) => $q->where('approval_status', $request->approval_status))
            ->when($request->filled('partner_id'), fn ($q) => $q->where('partner_id', $request->partner_id))
            ->when($request->filled('service_type'), fn ($q) => $q->where('service_type', $request->service_type))
            ->when($request->filled('zone_id'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('origin_zone_id', $request->zone_id)
                    ->orWhere('destination_zone_id', $request->zone_id);
            }))
            ->orderBy('created_at', 'desc');

        $rates = $query->paginate(20)->withQueryString();

        $partners = User::where('user_type', 'partner')->where('verification_status', 'approved')->get();
        $zones = DeliveryZone::where('is_active', true)->get();
        $serviceTypes = DomesticRate::getServiceTypeOptions();

        return view('admin.domestic.rates.index', compact('rates', 'partners', 'zones', 'serviceTypes'));
    }

    public function create()
    {
        $partners = User::where('user_type', 'partner')->get();
        $zones = DeliveryZone::where('is_active', true)->get();
        $serviceTypes = DomesticRate::getServiceTypeOptions();
        
        return view('admin.domestic.rates.create', compact('partners', 'zones', 'serviceTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->ensureNoOverlappingRate($request);

        $serviceName = DomesticRate::SERVICE_NAMES[$request->service_type]
            ?? LogisticsService::where('code', $request->service_type)->value('name')
            ?? ucwords(str_replace(['_', '-'], ' ', $request->service_type));

        $originZone = DeliveryZone::findOrFail($request->origin_zone_id);
        $destinationZone = DeliveryZone::findOrFail($request->destination_zone_id);

        $rate = DomesticRate::create([
            'partner_id' => $request->partner_id,
            'origin_zone_id' => $request->origin_zone_id,
            'destination_zone_id' => $request->destination_zone_id,
            // These are required by the original domestic_rates schema and
            // remain populated for backwards-compatible quote lookups.
            'origin_city' => $originZone->zone_name,
            'origin_zone' => $originZone->zone_code,
            'destination_city' => $destinationZone->zone_name,
            'destination_zone' => $destinationZone->zone_code,
            'service_type' => $request->service_type,
            'rate_type' => $request->input('rate_type', 'door_to_door'),
            'service_name' => $serviceName,
            'base_rate' => $request->base_rate,
            'per_kg_rate' => $request->per_kg_rate,
            'rate_per_kg' => $request->per_kg_rate,
            'per_km_rate' => $request->per_km_rate ?? 0,
            'minimum_rate' => $request->minimum_rate ?? 0,
            'logistical_charge' => $request->logistical_charge ?? 0,
            'pickup_charge' => $request->pickup_charge ?? 0,
            'origin_handling_charge' => $request->origin_handling_charge ?? 0,
            'destination_handling_charge' => $request->destination_handling_charge ?? 0,
            'remote_area_surcharge' => $request->remote_area_surcharge ?? 0,
            'cod_charge' => $request->cod_charge ?? 0,
            'additional_charge' => $request->additional_charge ?? 0,
            'additional_charge_reason' => $request->additional_charge_reason,
            'currency' => strtoupper($request->currency),
            'weight_from' => $request->weight_from,
            'weight_to' => $request->weight_to,
            'estimated_hours' => $request->estimated_hours,
            'estimated_days' => $request->estimated_days,
            'is_active' => true,
            'approval_status' => 'approved',
            'submitted_by' => $request->user()->id,
            'approved_by' => $request->user()->id,
            'submitted_at' => now(),
            'approved_at' => now(),
            'admin_margin_type' => $request->input('admin_margin_type', 'percentage'),
            'admin_margin_value' => $request->input('admin_margin_value', 10),
            'is_default_destination' => $request->boolean('is_default_destination'),
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
        ]);

        $this->recordEvent($rate, 'created_and_approved', $request->user()->id, 'Rate created by an administrator.');

        return redirect()->route('admin.domestic.rates')
            ->with('success', 'Domestic rate created successfully!');
    }

    public function edit($id)
    {
        $rate = DomesticRate::findOrFail($id);
        $partners = User::where('user_type', 'partner')->get();
        $zones = DeliveryZone::where('is_active', true)->get();
        $serviceTypes = DomesticRate::getServiceTypeOptions($rate->partner_id);
        
        return view('admin.domestic.rates.edit', compact('rate', 'partners', 'zones', 'serviceTypes'));
    }

    public function update(Request $request, $id)
    {
        $rate = DomesticRate::findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules(true));

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->ensureNoOverlappingRate($request, $rate->id);

        $serviceName = DomesticRate::SERVICE_NAMES[$request->service_type]
            ?? LogisticsService::where('code', $request->service_type)->value('name')
            ?? ucwords(str_replace(['_', '-'], ' ', $request->service_type));

        $originZone = DeliveryZone::findOrFail($request->origin_zone_id);
        $destinationZone = DeliveryZone::findOrFail($request->destination_zone_id);

        $before = $rate->toArray();
        $rate->update([
            'partner_id' => $request->partner_id,
            'origin_zone_id' => $request->origin_zone_id,
            'destination_zone_id' => $request->destination_zone_id,
            'origin_city' => $originZone->zone_name,
            'origin_zone' => $originZone->zone_code,
            'destination_city' => $destinationZone->zone_name,
            'destination_zone' => $destinationZone->zone_code,
            'service_type' => $request->service_type,
            'rate_type' => $request->input('rate_type', $rate->rate_type ?: 'door_to_door'),
            'service_name' => $serviceName,
            'base_rate' => $request->base_rate,
            'per_kg_rate' => $request->per_kg_rate,
            'rate_per_kg' => $request->per_kg_rate,
            'per_km_rate' => $request->per_km_rate ?? 0,
            'minimum_rate' => $request->minimum_rate ?? 0,
            'logistical_charge' => $request->logistical_charge ?? 0,
            'pickup_charge' => $request->pickup_charge ?? 0,
            'origin_handling_charge' => $request->origin_handling_charge ?? 0,
            'destination_handling_charge' => $request->destination_handling_charge ?? 0,
            'remote_area_surcharge' => $request->remote_area_surcharge ?? 0,
            'cod_charge' => $request->cod_charge ?? 0,
            'additional_charge' => $request->additional_charge ?? 0,
            'additional_charge_reason' => $request->additional_charge_reason,
            'currency' => strtoupper($request->currency),
            'weight_from' => $request->weight_from,
            'weight_to' => $request->weight_to,
            'estimated_hours' => $request->estimated_hours,
            'estimated_days' => $request->estimated_days,
            'is_active' => $request->has('is_active'),
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
            'admin_margin_type' => $request->input('admin_margin_type', $rate->admin_margin_type ?: 'percentage'),
            'admin_margin_value' => $request->input('admin_margin_value', $rate->admin_margin_value ?? 10),
            'is_default_destination' => $request->boolean('is_default_destination'),
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
        ]);


        $this->recordEvent($rate, 'updated_and_approved', $request->user()->id, 'Rate updated by an administrator.', $before);

        return redirect()->route('admin.domestic.rates')
            ->with('success', 'Domestic rate updated successfully!');
    }

    public function destroy($id)
    {
        $rate = DomesticRate::findOrFail($id);
        $rate->delete();

        return redirect()->route('admin.domestic.rates')
            ->with('success', 'Domestic rate deleted successfully!');
    }

    public function approve(Request $request, DomesticRate $rate)
    {
        $this->ensureNoOverlappingRate($request->merge($rate->only([
            'partner_id', 'origin_zone_id', 'destination_zone_id', 'service_type',
            'rate_type', 'weight_from', 'weight_to', 'effective_from', 'effective_to',
        ])), $rate->id);

        $before = $rate->toArray();
        $rate->update([
            'approval_status' => 'approved',
            'is_active' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
        $this->recordEvent($rate, 'approved', $request->user()->id, 'Partner rate approved.', $before);

        return back()->with('success', 'Partner rate approved and made available for quotes.');
    }

    public function reject(Request $request, DomesticRate $rate)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|min:5|max:1000']);
        $before = $rate->toArray();
        $rate->update([
            'approval_status' => 'rejected',
            'is_active' => false,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);
        $this->recordEvent($rate, 'rejected', $request->user()->id, $data['rejection_reason'], $before);

        return back()->with('success', 'Partner rate rejected. It will not appear in customer quotes.');
    }

    private function rules(bool $includeActive = false): array
    {
        $rules = [
            'partner_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('user_type', 'partner')->where('verification_status', 'approved'))],
            'origin_zone_id' => 'required|exists:delivery_zones,id',
            'destination_zone_id' => 'required|different:origin_zone_id|exists:delivery_zones,id',
            'service_type' => ['required', Rule::in(array_keys($this->serviceTypes()))],
            'rate_type' => 'nullable|in:pickup,logistics,delivery,door_to_door',
            'base_rate' => 'required|numeric|min:0',
            'per_kg_rate' => 'required|numeric|min:0',
            'per_km_rate' => 'nullable|numeric|min:0',
            'minimum_rate' => 'nullable|numeric|min:0',
            'logistical_charge' => 'nullable|numeric|min:0',
            'pickup_charge' => 'nullable|numeric|min:0',
            'origin_handling_charge' => 'nullable|numeric|min:0',
            'destination_handling_charge' => 'nullable|numeric|min:0',
            'remote_area_surcharge' => 'nullable|numeric|min:0',
            'cod_charge' => 'nullable|numeric|min:0',
            'additional_charge' => 'nullable|numeric|min:0',
            'additional_charge_reason' => 'nullable|string|max:1000',
            'admin_margin_type' => 'nullable|in:percentage,fixed',
            'admin_margin_value' => 'nullable|numeric|min:0|max:1000000',
            'is_default_destination' => 'nullable|boolean',
            'currency' => 'required|string|size:3',
            'weight_from' => 'required|numeric|min:0',
            'weight_to' => 'required|numeric|gt:weight_from',
            'estimated_hours' => 'nullable|integer|min:0',
            'estimated_days' => 'nullable|integer|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ];

        if ($includeActive) {
            $rules['is_active'] = 'nullable|boolean';
        }

        return $rules;
    }

    private function recordEvent(DomesticRate $rate, string $event, ?int $actorId, ?string $notes = null, ?array $before = null): void
    {
        DomesticRateEvent::create([
            'domestic_rate_id' => $rate->id,
            'event_type' => $event,
            'performed_by' => $actorId,
            'notes' => $notes,
            'before' => $before,
            'after' => $rate->fresh()->toArray(),
        ]);
    }

    private function serviceTypes(): array
    {
        $configured = LogisticsService::active()->category('domestic')->orderBy('sort_order')->get();
        if ($configured->isEmpty()) {
            return DomesticRate::getServiceTypeOptions();
        }

        return $configured->mapWithKeys(fn ($service) => [$service->code => [
            'name' => $service->name,
            'icon' => DomesticRate::SERVICE_ICONS[$service->code] ?? '📦',
            'time' => $service->transit_display,
        ]])->all();
    }

    /** Reject ambiguous weight/date bands before they can affect quoting. */
    private function ensureNoOverlappingRate(Request $request, ?int $ignoreRateId = null): void
    {
        $effectiveTo = $request->input('effective_to') ?: '9999-12-31';

        $query = DomesticRate::query()
            ->where('partner_id', $request->partner_id)
            ->where('origin_zone_id', $request->origin_zone_id)
            ->where('destination_zone_id', $request->destination_zone_id)
            ->where('service_type', $request->service_type)
            ->where('rate_type', $request->input('rate_type', 'door_to_door'))
            ->where('weight_from', '<=', $request->weight_to)
            ->where('weight_to', '>=', $request->weight_from)
            ->whereDate('effective_from', '<=', $effectiveTo)
            ->where(function ($query) use ($request) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $request->effective_from);
            });

        if ($ignoreRateId) {
            $query->whereKeyNot($ignoreRateId);
        }

        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'weight_from' => 'This rate overlaps an existing active or scheduled rate for the same partner, route, service, weight band, and effective period.',
            ]);
        }
    }
}
