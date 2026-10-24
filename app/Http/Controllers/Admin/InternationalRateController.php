<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalTariffSetting;
use App\Models\InternationalRate;
use App\Models\InternationalZone;
use App\Models\OverseasHub;
use App\Models\PackagingMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternationalRateController extends Controller
{
    public function __construct()
    {
        // Allowed for Super Admin, Admin, and International Admin/Staff
        $this->middleware(['auth', 'role:super_admin,admin,international_admin,staff']);
    }

    /**
     * Display a listing of international rate matrices.
     */
    public function index(Request $request)
    {
        $query = InternationalRate::with(['hub', 'agency', 'zone', 'creator']);

        if ($request->filled('hub_id')) {
            if ($request->hub_id === 'direct') {
                $query->whereNull('hub_id');
            } else {
                $query->where('hub_id', $request->hub_id);
            }
        }

        if ($request->filled('agency_id')) {
            $query->where('agency_id', $request->agency_id);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('rate_type')) {
            $query->where('rate_type', $request->rate_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('country', 'LIKE', "%{$search}%")
                  ->orWhereHas('zone', function ($zq) use ($search) {
                      $zq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('code', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('agency', function ($aq) use ($search) {
                      $aq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $rates = $query->orderBy('created_at', 'desc')->paginate(15);
        $hubs = OverseasHub::active()->orderBy('sort_order')->get();
        $agencies = \App\Models\Agency::where('is_active', true)->orderBy('name')->get();
        $zones = InternationalZone::active()->get();

        $stats = [
            'total_rates' => InternationalRate::count(),
            'country_rates' => InternationalRate::where('rate_type', 'country')->count(),
            'zone_rates' => InternationalRate::where('rate_type', 'zone')->count(),
            'active_rates' => InternationalRate::where('is_active', true)->count(),
        ];

        return view('admin.international-rates.index', compact('rates', 'hubs', 'agencies', 'zones', 'stats'));
    }

    /**
     * Show form for creating a new international rate.
     */
    public function create(Request $request)
    {
        $hubs = OverseasHub::with(['agencies' => fn($q) => $q->where('is_active', true)])->active()->orderBy('sort_order')->get();
        $zones = InternationalZone::active()->get();
        $agencies = \App\Models\Agency::where('is_active', true)->orderBy('name')->get();
        $preselectedHubId = $request->query('hub_id');

        $hubsJson = $hubs->map(fn($h) => [
            'id' => $h->id,
            'hub_code' => $h->hub_code,
            'hub_name' => $h->hub_name,
            'country' => $h->country,
            'mode_type' => $h->mode_type,
            'coverage_countries' => (array)($h->coverage_countries ?? []),
            'service_routes' => (array)($h->service_routes ?? []),
            'agencies' => $h->agencies->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'code' => $a->code,
            ])->values()->all(),
        ]);

        $defaultCustoms = (float) GlobalTariffSetting::getValue('default_customs_clearance_charge', 500.00);
        $defaultGodown = (float) GlobalTariffSetting::getValue('default_godown_charge', 300.00);

        return view('admin.international-rates.create', compact('hubs', 'agencies', 'zones', 'preselectedHubId', 'hubsJson', 'defaultCustoms', 'defaultGodown'));
    }

    /**
     * Store newly created rate matrix.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rate_type' => 'required|in:country,zone',
            'country' => 'required_if:rate_type,country|nullable|string|max:100',
            'country_code' => 'nullable|string|max:10',
            'zone_id' => 'required_if:rate_type,zone|nullable|exists:international_zones,id',
            'hub_id' => 'nullable|exists:overseas_hubs,id',
            'agency_id' => 'nullable|exists:agencies,id',
            'service_type' => 'required|in:express,economy',
            'weight_tiers' => 'nullable|array',
            'per_kg_tiers' => 'nullable|array',
            'customs_clearance_charge' => 'required|numeric|min:0',
            'godown_charge' => 'required|numeric|min:0',
            'fuel_surcharge_percent' => 'nullable|numeric|min:0',
            'doc_fee' => 'nullable|numeric|min:0',
            'transit_days_min' => 'required|integer|min:1',
            'transit_days_max' => 'required|integer|min:1|gte:transit_days_min',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active', true);

        // Format weight tiers: clean numeric keys (0.5, 1.0, ... 10.0)
        if (isset($validated['weight_tiers']) && is_array($validated['weight_tiers'])) {
            $cleanTiers = [];
            foreach ($validated['weight_tiers'] as $w => $val) {
                if (is_numeric($val) && (float)$val > 0) {
                    $key = number_format((float)$w, 1, '.', '');
                    $cleanTiers[$key] = round((float)$val, 2);
                }
            }
            ksort($cleanTiers, SORT_NUMERIC);
            $validated['weight_tiers'] = $cleanTiers;
        }

        // Format per-kg dynamic tiers
        if (isset($validated['per_kg_tiers']) && is_array($validated['per_kg_tiers'])) {
            $cleanRanges = [];
            foreach ($validated['per_kg_tiers'] as $range) {
                if (!empty($range['rate_per_kg']) && (float)$range['rate_per_kg'] > 0) {
                    $cleanRanges[] = [
                        'min_weight' => (float)($range['min_weight'] ?? 10.1),
                        'max_weight' => (float)($range['max_weight'] ?? 9999.0),
                        'rate_per_kg' => (float)$range['rate_per_kg'],
                    ];
                }
            }
            usort($cleanRanges, fn($a, $b) => $a['min_weight'] <=> $b['min_weight']);
            $validated['per_kg_tiers'] = $cleanRanges;
        }

        InternationalRate::create($validated);

        return redirect()->route('admin.international-rates.index')
            ->with('success', 'International rate matrix successfully configured.');
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $rate = InternationalRate::with('agency')->findOrFail($id);
        $hubs = OverseasHub::with(['agencies' => fn($q) => $q->where('is_active', true)])->active()->orderBy('sort_order')->get();
        $agencies = \App\Models\Agency::where('is_active', true)->orderBy('name')->get();
        $zones = InternationalZone::active()->get();

        $hubsJson = $hubs->map(fn($h) => [
            'id' => $h->id,
            'hub_code' => $h->hub_code,
            'hub_name' => $h->hub_name,
            'country' => $h->country,
            'mode_type' => $h->mode_type,
            'coverage_countries' => (array)($h->coverage_countries ?? []),
            'service_routes' => (array)($h->service_routes ?? []),
            'agencies' => $h->agencies->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'code' => $a->code,
            ])->values()->all(),
        ]);

        $defaultCustoms = (float) GlobalTariffSetting::getValue('default_customs_clearance_charge', 500.00);
        $defaultGodown = (float) GlobalTariffSetting::getValue('default_godown_charge', 300.00);

        return view('admin.international-rates.edit', compact('rate', 'hubs', 'agencies', 'zones', 'hubsJson', 'defaultCustoms', 'defaultGodown'));
    }

    /**
     * Update rate matrix.
     */
    public function update(Request $request, $id)
    {
        $rate = InternationalRate::findOrFail($id);

        $validated = $request->validate([
            'rate_type' => 'required|in:country,zone',
            'country' => 'required_if:rate_type,country|nullable|string|max:100',
            'country_code' => 'nullable|string|max:10',
            'zone_id' => 'required_if:rate_type,zone|nullable|exists:international_zones,id',
            'hub_id' => 'nullable|exists:overseas_hubs,id',
            'agency_id' => 'nullable|exists:agencies,id',
            'service_type' => 'required|in:express,economy',
            'weight_tiers' => 'nullable|array',
            'per_kg_tiers' => 'nullable|array',
            'customs_clearance_charge' => 'required|numeric|min:0',
            'godown_charge' => 'required|numeric|min:0',
            'fuel_surcharge_percent' => 'nullable|numeric|min:0',
            'doc_fee' => 'nullable|numeric|min:0',
            'transit_days_min' => 'required|integer|min:1',
            'transit_days_max' => 'required|integer|min:1|gte:transit_days_min',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if (isset($validated['weight_tiers']) && is_array($validated['weight_tiers'])) {
            $cleanTiers = [];
            foreach ($validated['weight_tiers'] as $w => $val) {
                if (is_numeric($val) && (float)$val > 0) {
                    $key = number_format((float)$w, 1, '.', '');
                    $cleanTiers[$key] = round((float)$val, 2);
                }
            }
            ksort($cleanTiers, SORT_NUMERIC);
            $validated['weight_tiers'] = $cleanTiers;
        }

        if (isset($validated['per_kg_tiers']) && is_array($validated['per_kg_tiers'])) {
            $cleanRanges = [];
            foreach ($validated['per_kg_tiers'] as $range) {
                if (!empty($range['rate_per_kg']) && (float)$range['rate_per_kg'] > 0) {
                    $cleanRanges[] = [
                        'min_weight' => (float)($range['min_weight'] ?? 10.1),
                        'max_weight' => (float)($range['max_weight'] ?? 9999.0),
                        'rate_per_kg' => (float)$range['rate_per_kg'],
                    ];
                }
            }
            usort($cleanRanges, fn($a, $b) => $a['min_weight'] <=> $b['min_weight']);
            $validated['per_kg_tiers'] = $cleanRanges;
        }

        $rate->update($validated);

        return redirect()->route('admin.international-rates.index')
            ->with('success', 'International rate matrix successfully updated.');
    }

    /**
     * Toggle rate active status.
     */
    public function toggle($id)
    {
        $rate = InternationalRate::findOrFail($id);
        $rate->update(['is_active' => !$rate->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $rate->is_active,
        ]);
    }

    /**
     * Delete rate matrix.
     */
     public function destroy($id)
     {
         $rate = InternationalRate::findOrFail($id);
         $rate->delete();

         return back()->with('success', 'Rate matrix deleted successfully.');
     }

    /**
     * Display Global Tariff Settings & Dynamic Packaging Management.
     */
    public function settings(Request $request)
    {
        $settings = GlobalTariffSetting::all()->keyBy('setting_key');
        $packagingMaterials = PackagingMaterial::orderBy('sort_order', 'asc')->get();

        $currentCustoms = (float) ($settings['default_customs_clearance_charge']->setting_value ?? 500.00);
        $currentGodown = (float) ($settings['default_godown_charge']->setting_value ?? 300.00);
        $customsNotice = $settings['customs_charge_notice']->setting_value ?? '';
        $godownNotice = $settings['godown_charge_notice']->setting_value ?? '';

        $totalRateMatrices = InternationalRate::count();

        return view('admin.international-rates.settings', compact(
            'settings',
            'packagingMaterials',
            'currentCustoms',
            'currentGodown',
            'customsNotice',
            'godownNotice',
            'totalRateMatrices'
        ));
    }

    /**
     * Update Super Admin Global Tariff Baseline Settings (Customs Clearance & Godown Charges).
     */
    public function updateTariffSettings(Request $request)
    {
        $validated = $request->validate([
            'default_customs_clearance_charge' => 'required|numeric|min:0',
            'default_godown_charge' => 'required|numeric|min:0',
            'customs_charge_notice' => 'nullable|string|max:500',
            'godown_charge_notice' => 'nullable|string|max:500',
            'sync_all_matrices' => 'nullable|boolean',
        ]);

        $userId = Auth::id();

        GlobalTariffSetting::setValue('default_customs_clearance_charge', $validated['default_customs_clearance_charge'], $userId);
        GlobalTariffSetting::setValue('default_godown_charge', $validated['default_godown_charge'], $userId);

        if (isset($validated['customs_charge_notice'])) {
            GlobalTariffSetting::setValue('customs_charge_notice', $validated['customs_charge_notice'], $userId);
        }

        if (isset($validated['godown_charge_notice'])) {
            GlobalTariffSetting::setValue('godown_charge_notice', $validated['godown_charge_notice'], $userId);
        }

        GlobalTariffSetting::clearCache();

        $syncedCount = 0;
        if ($request->boolean('sync_all_matrices')) {
            $syncedCount = InternationalRate::query()->update([
                'customs_clearance_charge' => $validated['default_customs_clearance_charge'],
                'godown_charge' => $validated['default_godown_charge'],
            ]);
        }

        $msg = 'Global customs clearance and godown charges successfully updated.';
        if ($syncedCount > 0) {
            $msg .= " Also synchronized across {$syncedCount} existing international rate matrices.";
        }

        return redirect()->route('admin.international-rates.settings')->with('success', $msg);
    }

    /**
     * Store new packaging material.
     */
    public function storePackaging(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:packaging_materials,code|alpha_dash',
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['icon'] = $validated['icon'] ?: 'box';

        PackagingMaterial::create($validated);

        return redirect()->route('admin.international-rates.settings')
            ->with('success', "Packaging material '{$validated['name']}' successfully added.");
    }

    /**
     * Update existing packaging material.
     */
    public function updatePackaging(Request $request, $id)
    {
        $material = PackagingMaterial::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['icon'] = $validated['icon'] ?: 'box';

        $material->update($validated);

        return redirect()->route('admin.international-rates.settings')
            ->with('success', "Packaging material '{$material->name}' successfully updated.");
    }

    /**
     * Toggle packaging material active status.
     */
    public function togglePackaging($id)
    {
        $material = PackagingMaterial::findOrFail($id);
        $material->update(['is_active' => !$material->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $material->is_active,
        ]);
    }

    /**
     * Delete packaging material.
     */
    public function destroyPackaging($id)
    {
        $material = PackagingMaterial::findOrFail($id);

        if ($material->code === 'none') {
            return back()->with('error', 'The default "No Packaging" base option cannot be deleted.');
        }

        $name = $material->name;
        $material->delete();

        return redirect()->route('admin.international-rates.settings')
            ->with('success', "Packaging material '{$name}' deleted successfully.");
    }
}
