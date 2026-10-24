<?php

namespace App\Http\Controllers;

use App\Models\InternationalRate;
use App\Models\InternationalZone;
use App\Models\OverseasHub;
use App\Services\InternationalRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RateInquiryController extends Controller
{
    protected InternationalRateService $rateService;

    public function __construct(InternationalRateService $rateService)
    {
        $this->rateService = $rateService;
    }

    /**
     * Display the Rate Inquiry Calculator Page.
     */
    public function index(Request $request): View
    {
        // Get all unique destination countries from active rates and zones
        $countryList = $this->getAvailableCountries();
        $packagingCatalog = $this->rateService->getPackagingCatalog();
        $hubs = OverseasHub::active()->orderBy('sort_order')->get();
        $defaultCustomsCharge = (float) \App\Models\GlobalTariffSetting::getValue('default_customs_clearance_charge', 500.00);
        $defaultGodownCharge = (float) \App\Models\GlobalTariffSetting::getValue('default_godown_charge', 300.00);

        // Optional pre-selected query parameters
        $initialCountry = $request->get('country', 'United States');
        $initialWeight = (float) $request->get('weight', 1.0);
        $initialPackaging = $request->get('packaging', 'none');
        $initialPickupType = $request->get('pickup_location_type', 'inside_ktm');
        $initialPickupCity = $request->get('pickup_city', 'Pokhara');

        // Regional pickup hubs across Nepal
        $regionalCities = [
            'Pokhara' => 'Pokhara Depot (Gandaki)',
            'Biratnagar' => 'Biratnagar / Itahari Hub (Koshi)',
            'Birgunj' => 'Birgunj Gateway (Madhesh)',
            'Butwal' => 'Butwal / Bhairahawa Hub (Lumbini)',
            'Chitwan' => 'Chitwan / Narayangarh Depot (Bagmati)',
            'Nepalgunj' => 'Nepalgunj Hub (Lumbini)',
            'Dhangadhi' => 'Dhangadhi Depot (Sudurpashchim)',
            'Surkhet' => 'Surkhet Depot (Karnali)',
            'Hetauda' => 'Hetauda Depot (Bagmati)',
            'Dharan' => 'Dharan Sub-Hub (Koshi)',
            'Janakpur' => 'Janakpur Hub (Madhesh)',
        ];

        // Pre-compute initial quote if country is specified
        $initialQuote = null;
        if (!empty($initialCountry)) {
            $initialQuote = $this->rateService->quote(
                $initialCountry,
                $initialWeight,
                null,
                null,
                null,
                $initialPackaging,
                null,
                null,
                $initialPickupType,
                $initialPickupType === 'outside_ktm' ? $initialPickupCity : 'Kathmandu Valley'
            );
        }

        return view('rates.inquiry', [
            'countryList' => $countryList,
            'packagingCatalog' => $packagingCatalog,
            'hubs' => $hubs,
            'defaultCustomsCharge' => $defaultCustomsCharge,
            'defaultGodownCharge' => $defaultGodownCharge,
            'initialCountry' => $initialCountry,
            'initialWeight' => $initialWeight,
            'initialPackaging' => $initialPackaging,
            'initialPickupType' => $initialPickupType,
            'initialPickupCity' => $initialPickupCity,
            'regionalCities' => $regionalCities,
            'initialQuote' => $initialQuote,
        ]);
    }

    /**
     * AJAX Endpoint for Live Rate Calculation.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0.05|max:5000',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'packaging' => 'nullable|string',
            'service_type' => 'nullable|string|in:express,economy',
            'godown_rate_per_kg' => 'nullable|numeric|min:0',
            'pickup_location_type' => 'nullable|string|in:inside_ktm,outside_ktm',
            'pickup_city' => 'nullable|string|max:100',
            'domestic_partner_id' => 'nullable|integer',
        ]);

        $manualGodown = (isset($validated['godown_rate_per_kg']) && is_numeric($validated['godown_rate_per_kg']))
            ? (float) $validated['godown_rate_per_kg']
            : null;

        $quote = $this->rateService->quote(
            $validated['country'],
            (float) $validated['weight'],
            isset($validated['length']) ? (float)$validated['length'] : null,
            isset($validated['width']) ? (float)$validated['width'] : null,
            isset($validated['height']) ? (float)$validated['height'] : null,
            $validated['packaging'] ?? 'none',
            $validated['service_type'] ?? null,
            $manualGodown,
            $validated['pickup_location_type'] ?? 'inside_ktm',
            $validated['pickup_city'] ?? null,
            isset($validated['domestic_partner_id']) ? (int)$validated['domestic_partner_id'] : null
        );

        return response()->json([
            'success' => true,
            'data' => $quote,
        ]);
    }

    /**
     * Compile a sorted list of all serviced countries.
     */
    private function getAvailableCountries(): array
    {
        $countries = collect();

        // Countries from Country-wise rates
        $directCountries = InternationalRate::active()
            ->where('rate_type', 'country')
            ->whereNotNull('country')
            ->pluck('country');
        $countries = $countries->merge($directCountries);

        // Countries from Zones
        $zoneCountries = InternationalZone::active()->get()->flatMap(function ($zone) {
            return is_array($zone->countries) ? $zone->countries : [];
        });
        $countries = $countries->merge($zoneCountries);

        // Standard Top Global Destinations fallback
        $fallback = [
            'United States', 'United Kingdom', 'Australia', 'Canada', 
            'United Arab Emirates', 'Germany', 'France', 'Japan', 
            'Saudi Arabia', 'Qatar', 'Kuwait', 'Oman', 'Bahrain',
            'New Zealand', 'Singapore', 'Malaysia', 'India', 'South Korea',
            'Netherlands', 'Italy', 'Spain', 'Switzerland', 'China'
        ];
        $countries = $countries->merge($fallback)->unique()->sort()->values();

        return $countries->all();
    }
}
