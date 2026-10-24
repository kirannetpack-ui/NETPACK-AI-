<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\DomesticRate;
use App\Models\DomesticRateEvent;
use App\Models\LogisticsService;
use App\Notifications\DomesticRateSubmittedNotification;
use App\Services\DomesticOperationsNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DomesticRateSubmissionController extends Controller
{
    private const RATE_TYPES = [
        'pickup' => 'Pickup',
        'logistics' => 'Inter-Zone Logistics',
        'delivery' => 'Last-Mile Delivery',
        'door_to_door' => 'Complete Door-to-Door',
    ];

    public function index(Request $request)
    {
        $partner = $this->partner($request);
        $rates = DomesticRate::with(['originZone', 'destinationZone'])
            ->where('partner_id', $partner->id)
            ->when($request->filled('status'), fn ($query) => $query->where('approval_status', $request->status))
            ->when($request->filled('service_type'), fn ($query) => $query->where('service_type', $request->service_type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('partner.rates.submission-index', [
            'rates' => $rates,
            'services' => $this->services(),
            'rateTypes' => self::RATE_TYPES,
            'partner' => $partner,
        ]);
    }

    public function create(Request $request)
    {
        $partner = $this->partner($request);

        return view('partner.rates.submission-create', [
            'originZones' => DeliveryZone::active()->partnerZones($partner->id)->orderBy('zone_name')->get(),
            'destinationZones' => DeliveryZone::active()->orderByRaw("CASE WHEN zone_name LIKE '%Kathmandu%' THEN 0 ELSE 1 END")->orderBy('zone_name')->get(),
            'services' => $this->services(),
            'rateTypes' => self::RATE_TYPES,
        ]);
    }

    public function store(Request $request, DomesticOperationsNotificationService $notifications)
    {
        $partner = $this->partner($request);
        $data = $this->validateRate($request, $partner->id);
        $this->ensureNoOverlap($data, $partner->id);

        $rate = DB::transaction(function () use ($data, $partner) {
            $origin = DeliveryZone::findOrFail($data['origin_zone_id']);
            $destination = DeliveryZone::findOrFail($data['destination_zone_id']);
            $rate = DomesticRate::create(array_merge($this->withDefaults($data), [
                'partner_id' => $partner->id,
                'service_name' => $this->serviceName($data['service_type']),
                'origin_city' => $origin->zone_name,
                'origin_zone' => $origin->zone_code,
                'destination_city' => $destination->zone_name,
                'destination_zone' => $destination->zone_code,
                'rate_per_kg' => $data['per_kg_rate'],
                'approval_status' => 'pending',
                'is_active' => false,
                'submitted_by' => $partner->id,
                'submitted_at' => now(),
            ]));

            DomesticRateEvent::create([
                'domestic_rate_id' => $rate->id,
                'event_type' => 'submitted',
                'performed_by' => $partner->id,
                'after' => $rate->getAttributes(),
            ]);

            return $rate;
        });

        $notifications->notify(new DomesticRateSubmittedNotification($rate->load(['partner', 'originZone', 'destinationZone'])));

        return redirect()->route('partner.rates.index')->with('success', 'Rate submitted for administrator approval. It will not be quoted until approved.');
    }

    public function edit(Request $request, int $id)
    {
        $partner = $this->partner($request);
        $rate = DomesticRate::where('partner_id', $partner->id)->findOrFail($id);

        return view('partner.rates.submission-edit', [
            'rate' => $rate,
            'originZones' => DeliveryZone::active()->partnerZones($partner->id)->orderBy('zone_name')->get(),
            'destinationZones' => DeliveryZone::active()->orderBy('zone_name')->get(),
            'services' => $this->services(),
            'rateTypes' => self::RATE_TYPES,
        ]);
    }

    public function update(Request $request, int $id, DomesticOperationsNotificationService $notifications)
    {
        $partner = $this->partner($request);
        $rate = DomesticRate::where('partner_id', $partner->id)->findOrFail($id);
        $data = $this->validateRate($request, $partner->id);
        $this->ensureNoOverlap($data, $partner->id, $rate->id);

        DB::transaction(function () use ($rate, $data, $partner) {
            $before = $rate->getAttributes();
            $origin = DeliveryZone::findOrFail($data['origin_zone_id']);
            $destination = DeliveryZone::findOrFail($data['destination_zone_id']);
            $rate->update(array_merge($this->withDefaults($data), [
                'service_name' => $this->serviceName($data['service_type']),
                'origin_city' => $origin->zone_name,
                'origin_zone' => $origin->zone_code,
                'destination_city' => $destination->zone_name,
                'destination_zone' => $destination->zone_code,
                'rate_per_kg' => $data['per_kg_rate'],
                'approval_status' => 'pending',
                'is_active' => false,
                'submitted_by' => $partner->id,
                'submitted_at' => now(),
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null,
            ]));

            DomesticRateEvent::create([
                'domestic_rate_id' => $rate->id,
                'event_type' => 'resubmitted',
                'performed_by' => $partner->id,
                'before' => $before,
                'after' => $rate->fresh()->getAttributes(),
            ]);
        });

        $notifications->notify(new DomesticRateSubmittedNotification($rate->fresh()->load(['partner', 'originZone', 'destinationZone'])));

        return redirect()->route('partner.rates.index')->with('success', 'Updated rate submitted for administrator approval.');
    }

    private function validateRate(Request $request, int $partnerId): array
    {
        return $request->validate([
            'origin_zone_id' => ['required', Rule::exists('delivery_zones', 'id')->where(fn ($query) => $query->where('partner_user_id', $partnerId)->where('is_active', true))],
            'destination_zone_id' => ['required', 'different:origin_zone_id', Rule::exists('delivery_zones', 'id')->where('is_active', true)],
            'rate_type' => ['required', Rule::in(array_keys(self::RATE_TYPES))],
            'service_type' => ['required', Rule::in(array_keys($this->services()))],
            'weight_from' => 'required|numeric|min:0',
            'weight_to' => 'required|numeric|gt:weight_from',
            'base_rate' => 'required|numeric|min:0',
            'per_kg_rate' => 'required|numeric|min:0',
            'per_km_rate' => 'nullable|numeric|min:0',
            'minimum_rate' => 'nullable|numeric|min:0',
            'pickup_charge' => 'nullable|numeric|min:0',
            'logistical_charge' => 'nullable|numeric|min:0',
            'origin_handling_charge' => 'nullable|numeric|min:0',
            'destination_handling_charge' => 'nullable|numeric|min:0',
            'remote_area_surcharge' => 'nullable|numeric|min:0',
            'cod_charge' => 'nullable|numeric|min:0',
            'additional_charge' => 'nullable|numeric|min:0',
            'additional_charge_reason' => 'nullable|string|max:1000',
            'currency' => 'required|string|size:3',
            'estimated_hours' => 'nullable|integer|min:1',
            'estimated_days' => 'nullable|integer|min:1',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'is_default_destination' => 'nullable|boolean',
        ]);
    }

    private function withDefaults(array $data): array
    {
        foreach (['per_km_rate', 'minimum_rate', 'pickup_charge', 'logistical_charge', 'origin_handling_charge', 'destination_handling_charge', 'remote_area_surcharge', 'cod_charge', 'additional_charge'] as $field) {
            $data[$field] = $data[$field] ?? 0;
        }
        $data['currency'] = strtoupper($data['currency']);
        $data['is_default_destination'] = (bool) ($data['is_default_destination'] ?? false);

        return $data;
    }

    private function ensureNoOverlap(array $data, int $partnerId, ?int $ignoreRateId = null): void
    {
        $query = DomesticRate::where('partner_id', $partnerId)
            ->where('origin_zone_id', $data['origin_zone_id'])
            ->where('destination_zone_id', $data['destination_zone_id'])
            ->where('service_type', $data['service_type'])
            ->where('rate_type', $data['rate_type'])
            ->whereIn('approval_status', ['pending', 'approved'])
            ->where('weight_from', '<=', $data['weight_to'])
            ->where('weight_to', '>=', $data['weight_from'])
            ->whereDate('effective_from', '<=', $data['effective_to'] ?? '9999-12-31')
            ->where(fn ($dates) => $dates->whereNull('effective_to')->orWhereDate('effective_to', '>=', $data['effective_from']));

        if ($ignoreRateId) {
            $query->whereKeyNot($ignoreRateId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'weight_from' => 'This submission conflicts with an existing pending or approved rate for the same route, service, rate type, weight band and effective dates.',
            ]);
        }
    }

    private function partner(Request $request)
    {
        abort_unless($request->user()?->user_type === 'partner', 403);

        return $request->user();
    }

    private function services(): array
    {
        $services = LogisticsService::active()->category('domestic')->orderBy('sort_order')->get();
        if ($services->isNotEmpty()) {
            return $services->mapWithKeys(fn ($service) => [$service->code => $service->name])->all();
        }

        return collect(DomesticRate::getServiceTypeOptions())->mapWithKeys(fn ($service, $code) => [$code => $service['name']])->all();
    }

    private function serviceName(string $code): string
    {
        return $this->services()[$code] ?? strtoupper(str_replace('_', ' ', $code));
    }
}
