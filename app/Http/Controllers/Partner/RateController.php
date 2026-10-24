<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\DomesticRate;
use App\Models\LogisticsService;
use App\Models\ReminderLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function getPartnerId(): int
    {
        $user = Auth::user();
        if ($user->user_type !== 'partner') {
            abort(403, 'Unauthorized access. Partner only area.');
        }
        return (int) $user->id;
    }

    private function getPartner(): User
    {
        return Auth::user();
    }

    /**
     * Display the unified Single Platform for all service rates across partner's zones.
     */
    public function index(Request $request)
    {
        $partnerId = $this->getPartnerId();
        $partner = $this->getPartner();

        $zones = DeliveryZone::where('partner_user_id', $partnerId)
            ->orderBy('zone_name')
            ->get();

        // Standard Catalog Services
        $standardServices = [
            'standard' => [
                'code' => 'standard',
                'label' => 'STANDARD EXPRESS',
                'icon' => 'fa-truck',
                'color' => 'blue',
                'active' => true,
                'default_hours' => 24,
                'description' => 'Economy domestic courier across cities & districts (1-2 days)',
                'is_custom' => false,
            ],
            'flash' => [
                'code' => 'flash',
                'label' => 'FLASH DELIVERY',
                'icon' => 'fa-bolt',
                'color' => 'red',
                'active' => $partner->flash_active ?? true,
                'default_hours' => 4,
                'description' => 'Ultra-fast express point-to-point dispatch within 2-4 hours',
                'is_custom' => false,
            ],
            'same_day' => [
                'code' => 'same_day',
                'label' => 'SAME DAY DISPATCH',
                'icon' => 'fa-clock',
                'color' => 'amber',
                'active' => $partner->same_day_active ?? true,
                'default_hours' => 12,
                'description' => 'Guaranteed same-day delivery by 8 PM for daytime bookings',
                'is_custom' => false,
            ],
            'himalayan' => [
                'code' => 'himalayan',
                'label' => 'HIMALAYAN / REMOTE',
                'icon' => 'fa-mountain',
                'color' => 'purple',
                'active' => $partner->himalayan_active ?? true,
                'default_hours' => 72,
                'description' => 'Rugged linehaul delivery into mountain & hilly provincial pockets',
                'is_custom' => false,
            ],
        ];

        // Partner Custom Services
        $customServices = LogisticsService::where('partner_id', $partnerId)
            ->where('category', 'domestic')
            ->orderBy('sort_order', 'asc')
            ->get()
            ->mapWithKeys(function ($svc) {
                return [
                    $svc->code => [
                        'code' => $svc->code,
                        'label' => strtoupper($svc->name),
                        'icon' => 'fa-star',
                        'color' => 'emerald',
                        'active' => (bool)$svc->is_active,
                        'default_hours' => (int)$svc->transit_time_hours,
                        'description' => $svc->description ?: 'Partner specialized custom service',
                        'is_custom' => true,
                        'service_id' => $svc->id,
                    ]
                ];
            })->toArray();

        $services = array_merge($standardServices, $customServices);

        // Fetch all current domestic rates for this partner indexed by zone_id & service_type
        $existingRates = DomesticRate::where('partner_id', $partnerId)
            ->get()
            ->groupBy(function ($item) {
                $zoneId = $item->destination_zone_id ?: $item->origin_zone_id;
                return "{$zoneId}_{$item->service_type}";
            });

        // Assemble rate matrix for each zone and service
        $rateMatrix = [];
        foreach ($zones as $zone) {
            $rateMatrix[$zone->id] = [];
            foreach ($services as $code => $svc) {
                $key = "{$zone->id}_{$code}";
                $dbRate = $existingRates->get($key)?->first();

                if ($dbRate) {
                    $baseRate = (float)$dbRate->base_rate;
                    $perKgRate = (float)$dbRate->per_kg_rate;
                    $estHours = (int)($dbRate->estimated_hours ?: $svc['default_hours']);
                    $isActive = (bool)$dbRate->is_active;
                } else {
                    // Fallback to zone column rates for standard services
                    $legacy = $zone->getServiceRates($code);
                    $baseRate = (float)($legacy['base_rate'] ?? 0);
                    $perKgRate = (float)($legacy['per_kg_rate'] ?? 0);
                    $estHours = (int)($legacy['estimated_hours'] ?? $svc['default_hours']);
                    $isActive = (bool)$svc['active'];
                }

                $rateMatrix[$zone->id][$code] = [
                    'base_rate' => $baseRate,
                    'base_weight_limit' => 1.0, // 1.0 kg standard base tier
                    'per_kg_rate' => $perKgRate,
                    'estimated_hours' => $estHours,
                    'is_active' => $isActive,
                ];
            }
        }

        $selectedZoneId = (int)$request->get('zone', $zones->first()?->id ?? 0);
        $selectedZone = $zones->firstWhere('id', $selectedZoneId) ?? $zones->first();

        return view('partner.rates.index', compact(
            'zones',
            'services',
            'partner',
            'rateMatrix',
            'selectedZone'
        ));
    }

    /**
     * Show the form for editing rates for a specific zone.
     */
    public function edit($id)
    {
        $partnerId = $this->getPartnerId();
        $partner = $this->getPartner();

        $zone = DeliveryZone::where('partner_user_id', $partnerId)->findOrFail($id);

        // Standard Services
        $services = [
            'standard' => [
                'code' => 'standard',
                'label' => 'STANDARD EXPRESS',
                'icon' => 'fa-truck',
                'color' => 'blue',
                'active' => true,
                'default_hours' => 24,
                'description' => 'Economy domestic courier across cities & districts',
                'rates' => $this->resolveServiceRates($partnerId, $zone, 'standard'),
            ],
            'flash' => [
                'code' => 'flash',
                'label' => 'FLASH DELIVERY',
                'icon' => 'fa-bolt',
                'color' => 'red',
                'active' => $partner->flash_active ?? true,
                'default_hours' => 4,
                'description' => 'Ultra-fast express point-to-point dispatch within 2-4 hours',
                'rates' => $this->resolveServiceRates($partnerId, $zone, 'flash'),
            ],
            'same_day' => [
                'code' => 'same_day',
                'label' => 'SAME DAY DISPATCH',
                'icon' => 'fa-clock',
                'color' => 'amber',
                'active' => $partner->same_day_active ?? true,
                'default_hours' => 12,
                'description' => 'Guaranteed same-day delivery by 8 PM for daytime bookings',
                'rates' => $this->resolveServiceRates($partnerId, $zone, 'same_day'),
            ],
            'himalayan' => [
                'code' => 'himalayan',
                'label' => 'HIMALAYAN / REMOTE',
                'icon' => 'fa-mountain',
                'color' => 'purple',
                'active' => $partner->himalayan_active ?? true,
                'default_hours' => 72,
                'description' => 'Rugged linehaul delivery into mountain & hilly provincial pockets',
                'rates' => $this->resolveServiceRates($partnerId, $zone, 'himalayan'),
            ],
        ];

        // Custom Services
        $customServices = LogisticsService::where('partner_id', $partnerId)
            ->where('category', 'domestic')
            ->get();

        foreach ($customServices as $custom) {
            $services[$custom->code] = [
                'code' => $custom->code,
                'label' => strtoupper($custom->name),
                'icon' => 'fa-star',
                'color' => 'emerald',
                'active' => (bool)$custom->is_active,
                'default_hours' => (int)$custom->transit_time_hours,
                'description' => $custom->description ?: 'Partner custom service',
                'rates' => $this->resolveServiceRates($partnerId, $zone, $custom->code),
            ];
        }

        return view('partner.rates.edit', compact('zone', 'services', 'partner'));
    }

    /**
     * Update rates for a specific zone with Base Price and Weight-wise rates.
     */
    public function update(Request $request, $id)
    {
        $partnerId = $this->getPartnerId();
        $zone = DeliveryZone::where('partner_user_id', $partnerId)->findOrFail($id);

        $request->validate([
            'rates' => 'nullable|array',
            'flash_base_rate' => 'nullable|numeric|min:0',
            'flash_per_kg_rate' => 'nullable|numeric|min:0',
            'flash_estimated_hours' => 'nullable|integer|min:0',
            'same_day_base_rate' => 'nullable|numeric|min:0',
            'same_day_per_kg_rate' => 'nullable|numeric|min:0',
            'same_day_estimated_hours' => 'nullable|integer|min:0',
            'standard_base_rate' => 'nullable|numeric|min:0',
            'standard_per_kg_rate' => 'nullable|numeric|min:0',
            'standard_estimated_hours' => 'nullable|integer|min:0',
            'himalayan_base_rate' => 'nullable|numeric|min:0',
            'himalayan_per_kg_rate' => 'nullable|numeric|min:0',
            'himalayan_estimated_hours' => 'nullable|integer|min:0',
        ]);

        $changes = [];

        // 1. Process structured rates array if provided by modern UI
        if ($request->has('rates') && is_array($request->input('rates'))) {
            foreach ($request->input('rates') as $serviceCode => $rateData) {
                $baseRate = (float)($rateData['base_rate'] ?? 0);
                $perKgRate = (float)($rateData['per_kg_rate'] ?? 0);
                $hours = isset($rateData['estimated_hours']) && $rateData['estimated_hours'] !== ''
                    ? (int)$rateData['estimated_hours']
                    : null;
                $isActive = !empty($rateData['is_active']);

                $this->syncDomesticRateRecord($partnerId, $zone, $serviceCode, $baseRate, $perKgRate, $hours, $isActive);
            }
        }

        // 2. Backward compatibility: update zone table columns
        $legacyUpdateData = [
            'flash_base_rate' => $request->input('rates.flash.base_rate', $request->flash_base_rate ?? $zone->flash_base_rate),
            'flash_per_kg_rate' => $request->input('rates.flash.per_kg_rate', $request->flash_per_kg_rate ?? $zone->flash_per_kg_rate),
            'flash_estimated_hours' => $request->input('rates.flash.estimated_hours', $request->flash_estimated_hours ?? $zone->flash_estimated_hours),

            'same_day_base_rate' => $request->input('rates.same_day.base_rate', $request->same_day_base_rate ?? $zone->same_day_base_rate),
            'same_day_per_kg_rate' => $request->input('rates.same_day.per_kg_rate', $request->same_day_per_kg_rate ?? $zone->same_day_per_kg_rate),
            'same_day_estimated_hours' => $request->input('rates.same_day.estimated_hours', $request->same_day_estimated_hours ?? $zone->same_day_estimated_hours),

            'standard_base_rate' => $request->input('rates.standard.base_rate', $request->standard_base_rate ?? $zone->standard_base_rate),
            'standard_per_kg_rate' => $request->input('rates.standard.per_kg_rate', $request->standard_per_kg_rate ?? $zone->standard_per_kg_rate),
            'standard_estimated_hours' => $request->input('rates.standard.estimated_hours', $request->standard_estimated_hours ?? $zone->standard_estimated_hours),

            'himalayan_base_rate' => $request->input('rates.himalayan.base_rate', $request->himalayan_base_rate ?? $zone->himalayan_base_rate),
            'himalayan_per_kg_rate' => $request->input('rates.himalayan.per_kg_rate', $request->himalayan_per_kg_rate ?? $zone->himalayan_per_kg_rate),
            'himalayan_estimated_hours' => $request->input('rates.himalayan.estimated_hours', $request->himalayan_estimated_hours ?? $zone->himalayan_estimated_hours),
        ];

        // Also sync domestic_rates for standard fields if posted as top-level fields
        foreach (['flash', 'same_day', 'standard', 'himalayan'] as $svcKey) {
            $bRate = (float)($legacyUpdateData["{$svcKey}_base_rate"] ?? 0);
            $pRate = (float)($legacyUpdateData["{$svcKey}_per_kg_rate"] ?? 0);
            $hrs = (int)($legacyUpdateData["{$svcKey}_estimated_hours"] ?? null);

            $this->syncDomesticRateRecord($partnerId, $zone, $svcKey, $bRate, $pRate, $hrs, true);
        }

        $rateChanges = $this->getRateChanges($zone, $legacyUpdateData);
        $zone->update($legacyUpdateData);

        if (!empty($rateChanges)) {
            $this->notifyAdminAboutRateChange($zone, $rateChanges);
        }

        return redirect()->route('partner.rates.index', ['zone' => $zone->id])
            ->with('success', "Service rates for zone '{$zone->zone_name}' updated successfully with Base Price and Weight-wise rates!");
    }

    /**
     * Allow Domestic Partner to create a new custom service offering.
     */
    public function storeCustomService(Request $request)
    {
        $partnerId = $this->getPartnerId();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|alpha_dash|unique:logistics_services,code',
            'transit_time_hours' => 'required|numeric|min:0.5|max:2160',
            'base_rate' => 'required|numeric|min:0',
            'per_kg_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $code = Str::slug($validated['code'], '_');
        $hours = (float)$validated['transit_time_hours'];
        $days = round($hours / 24, 2);

        // 1. Create LogisticsService definition
        $service = LogisticsService::create([
            'partner_id' => $partnerId,
            'name' => $validated['name'],
            'code' => $code,
            'category' => 'domestic',
            'transit_time_hours' => $hours,
            'transit_time_days' => $days,
            'base_rate' => (float)$validated['base_rate'],
            'per_kg_rate' => (float)$validated['per_kg_rate'],
            'description' => $validated['description'] ?? "Specialized partner service: {$validated['name']}",
            'is_active' => true,
            'sort_order' => 10,
        ]);

        // 2. Initialize DomesticRate for all partner's zones
        $partnerZones = DeliveryZone::where('partner_user_id', $partnerId)->get();
        if ($partnerZones->isEmpty()) {
            $partnerZones = DeliveryZone::where('is_active', true)->get();
        }

        foreach ($partnerZones as $zone) {
            $this->syncDomesticRateRecord(
                $partnerId,
                $zone,
                $code,
                (float)$validated['base_rate'],
                (float)$validated['per_kg_rate'],
                (int)$hours,
                true,
                $validated['name']
            );
        }

        return redirect()->route('partner.rates.index')
            ->with('success', "New custom service '{$service->name}' successfully introduced and provisioned across your delivery zones!");
    }

    /**
     * Helper to retrieve rates with safe fallback.
     */
    private function resolveServiceRates(int $partnerId, DeliveryZone $zone, string $serviceCode): array
    {
        $dbRate = DomesticRate::where('partner_id', $partnerId)
            ->where(function ($q) use ($zone) {
                $q->where('destination_zone_id', $zone->id)
                  ->orWhere('origin_zone_id', $zone->id);
            })
            ->where('service_type', $serviceCode)
            ->first();

        if ($dbRate) {
            return [
                'base_rate' => (float)$dbRate->base_rate,
                'per_kg_rate' => (float)$dbRate->per_kg_rate,
                'estimated_hours' => (int)$dbRate->estimated_hours,
                'is_active' => (bool)$dbRate->is_active,
            ];
        }

        $legacy = $zone->getServiceRates($serviceCode);
        return [
            'base_rate' => (float)($legacy['base_rate'] ?? 0),
            'per_kg_rate' => (float)($legacy['per_kg_rate'] ?? 0),
            'estimated_hours' => (int)($legacy['estimated_hours'] ?? 24),
            'is_active' => true,
        ];
    }

    /**
     * Synchronize a DomesticRate record ensuring canonical pricing data.
     */
    private function syncDomesticRateRecord(
        int $partnerId,
        DeliveryZone $zone,
        string $serviceCode,
        float $baseRate,
        float $perKgRate,
        ?int $estimatedHours,
        bool $isActive,
        ?string $customName = null
    ): DomesticRate {
        $serviceNames = [
            'standard' => 'STANDARD',
            'flash' => 'FLASH',
            'same_day' => 'SAME DAY',
            'himalayan' => 'HIMALAYAN',
        ];

        $serviceName = $customName ?: ($serviceNames[$serviceCode] ?? ucwords(str_replace(['_', '-'], ' ', $serviceCode)));

        return DomesticRate::updateOrCreate(
            [
                'partner_id' => $partnerId,
                'destination_zone_id' => $zone->id,
                'service_type' => $serviceCode,
            ],
            [
                'origin_zone_id' => $zone->id,
                'origin_city' => $zone->zone_name,
                'origin_zone' => $zone->zone_code,
                'destination_city' => $zone->zone_name,
                'destination_zone' => $zone->zone_code,
                'service_name' => $serviceName,
                'base_rate' => $baseRate,
                'per_kg_rate' => $perKgRate,
                'rate_per_kg' => $perKgRate,
                'weight_from' => 0.00,
                'weight_to' => 1.00, // Standard base weight covers first 1.0 kg
                'estimated_hours' => $estimatedHours,
                'estimated_days' => $estimatedHours ? (int)ceil($estimatedHours / 24) : null,
                'is_active' => $isActive,
                'currency' => 'NPR',
                'effective_from' => now()->toDateString(),
            ]
        );
    }

    /**
     * Get rate changes between old and new values.
     */
    private function getRateChanges(DeliveryZone $zone, array $newData): array
    {
        $rateFields = [
            'flash_base_rate' => 'Flash Base Rate',
            'flash_per_kg_rate' => 'Flash Per KG Rate',
            'flash_estimated_hours' => 'Flash Estimated Hours',
            'same_day_base_rate' => 'Same Day Base Rate',
            'same_day_per_kg_rate' => 'Same Day Per KG Rate',
            'same_day_estimated_hours' => 'Same Day Estimated Hours',
            'standard_base_rate' => 'Standard Base Rate',
            'standard_per_kg_rate' => 'Standard Per KG Rate',
            'standard_estimated_hours' => 'Standard Estimated Hours',
            'himalayan_base_rate' => 'Himalayan Base Rate',
            'himalayan_per_kg_rate' => 'Himalayan Per KG Rate',
            'himalayan_estimated_hours' => 'Himalayan Estimated Hours',
        ];

        $changes = [];
        foreach ($rateFields as $field => $label) {
            $oldValue = $zone->$field;
            $newValue = $newData[$field] ?? null;

            if ($newValue !== null && (float)$oldValue != (float)$newValue) {
                $changes[$field] = [
                    'label' => $label,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Notify admins about rate changes.
     */
    private function notifyAdminAboutRateChange(DeliveryZone $zone, array $changes): void
    {
        $admins = User::whereIn('user_type', ['admin', 'super_admin', 'domestic_admin'])->get();
        $partner = $this->getPartner();

        $message = "📋 RATE CHANGE NOTIFICATION\n\n";
        $message .= "Partner: {$partner->name} ({$partner->email})\n";
        $message .= "Zone: {$zone->zone_name} ({$zone->zone_code})\n\n";
        $message .= "Changes made:\n";

        foreach ($changes as $change) {
            $message .= "  • {$change['label']}: Rs. {$change['old']} → Rs. {$change['new']}\n";
        }

        $message .= "\nTime: " . now()->format('Y-m-d H:i:s');

        foreach ($admins as $admin) {
            ReminderLog::create([
                'pickup_request_id' => null,
                'reminder_id' => null,
                'reminder_type' => 'admin_alert',
                'sent_to' => $admin->email,
                'message' => $message,
                'channel' => 'database',
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'zone_id' => $zone->id,
                    'partner_id' => $zone->partner_user_id,
                    'partner_name' => $partner->name,
                    'zone_name' => $zone->zone_name,
                    'changes' => $changes,
                ],
            ]);
        }
    }
}