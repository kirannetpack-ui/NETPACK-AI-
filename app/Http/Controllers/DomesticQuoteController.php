<?php

namespace App\Http\Controllers;

use App\Models\DomesticRate;
use App\Services\DomesticRateQuoteService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DomesticQuoteController extends Controller
{
    public function store(Request $request, DomesticRateQuoteService $quotes)
    {
        $data = $request->validate([
            'origin_zone_id' => 'required|integer|exists:delivery_zones,id',
            'destination_zone_id' => 'required|integer|different:origin_zone_id|exists:delivery_zones,id',
            'service_type' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0.1|max:100000',
            'is_cod' => 'nullable|boolean',
        ]);

        $quote = $quotes->quote(
            (int) $data['origin_zone_id'],
            (int) $data['destination_zone_id'],
            $data['service_type'],
            (float) $data['weight'],
            null,
            (bool) ($data['is_cod'] ?? false)
        );

        return response()->json([
            'customer_price' => $quote['customer_price'],
            'currency' => $quote['currency'],
            'estimated_hours' => $quote['estimated_hours'],
            'plan_type' => $quote['plan_type'],
            'expires_at' => $quote['expires_at'],
        ]);
    }
}
