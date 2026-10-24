<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RateCalculationService;
use Illuminate\Http\Request;

class RateCalculationController extends Controller
{
    protected $rateService;

    public function __construct(RateCalculationService $rateService)
    {
        $this->rateService = $rateService;
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'overseas_partner_id' => 'required|exists:users,id',
            'country_to' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'service_type' => 'required|string',
        ]);

        $params = $request->only(['overseas_partner_id', 'country_to', 'weight', 'service_type']);
        $result = $this->rateService->calculateRate($params);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No rate found for the given parameters',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}