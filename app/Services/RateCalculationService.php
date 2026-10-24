<?php

namespace App\Services;

use App\Models\OverseasBaseRate;
use App\Models\OverseasSubRate;
use App\Models\OverseasMarginRule;

class RateCalculationService
{
    public function calculateRate($params)
    {
        // Parameters: country_to, weight, service_type, overseas_partner_id
        $baseRate = $this->getBaseRate($params);
        if (!$baseRate) {
            return null;
        }

        $baseAmount = $baseRate->calculateBaseRate($params['weight']);

        // Calculate sub-rates (additional charges)
        $subRates = $this->getSubRates($params);
        $subTotal = 0;
        $subRateDetails = [];

        foreach ($subRates as $subRate) {
            $charge = $subRate->calculateCharge($baseAmount, $params['weight']);
            $subTotal += $charge;
            $subRateDetails[] = [
                'name' => $subRate->charge_name,
                'code' => $subRate->charge_code,
                'type' => $subRate->charge_type,
                'amount' => $charge,
            ];
        }

        // Calculate margin
        $margin = $this->getMargin($params);
        $marginAmount = $margin ? $margin->calculateMargin($baseAmount + $subTotal) : 0;

        $total = $baseAmount + $subTotal + $marginAmount;

        return [
            'base_rate' => [
                'amount' => $baseAmount,
                'rate_per_kg' => $baseRate->rate_per_kg,
                'weight' => $params['weight'],
                'minimum_rate' => $baseRate->minimum_rate,
            ],
            'sub_rates' => [
                'total' => $subTotal,
                'details' => $subRateDetails,
            ],
            'margin' => [
                'amount' => $marginAmount,
                'type' => $margin ? $margin->margin_type : null,
                'value' => $margin ? $margin->margin_value : null,
                'rule_name' => $margin ? $margin->rule_name : null,
            ],
            'total' => $total,
            'breakdown' => [
                'base_amount' => $baseAmount,
                'sub_charges' => $subTotal,
                'margin' => $marginAmount,
                'grand_total' => $total,
            ],
        ];
    }

    public function getBaseRate($params)
    {
        return OverseasBaseRate::active()
            ->where('overseas_partner_id', $params['overseas_partner_id'])
            ->where('country_to', $params['country_to'])
            ->where('service_type', $params['service_type'])
            ->where('weight_from', '<=', $params['weight'])
            ->where('weight_to', '>=', $params['weight'])
            ->first();
    }

    public function getSubRates($params)
    {
        return OverseasSubRate::active()
            ->where('overseas_partner_id', $params['overseas_partner_id'])
            ->byCountry($params['country_to'])
            ->byService($params['service_type'])
            ->byWeight($params['weight'])
            ->get();
    }

    public function getMargin($params)
    {
        return OverseasMarginRule::active()
            ->where('overseas_partner_id', $params['overseas_partner_id'])
            ->byCountry($params['country_to'])
            ->byService($params['service_type'])
            ->byWeight($params['weight'])
            ->first();
    }
}