<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrackingNumberService
{
    public function domestic(): string
    {
        return $this->tracking('domestic');
    }

    public function ecommerce(): string
    {
        return $this->tracking('ecommerce');
    }

    public function international(): string
    {
        return $this->tracking('international');
    }

    public function tracking(string $service): string
    {
        $prefix = config("tracking.tracking_prefixes.{$service}");

        if (!$prefix) {
            throw new InvalidArgumentException("Unknown tracking service: {$service}");
        }

        $year = (int) now()->format('Y');
        $sequence = $this->nextSequence("tracking:{$service}", $year);
        $serial = str_pad((string) $sequence, (int) config('tracking.sequence_width', 6), '0', STR_PAD_LEFT);
        $payload = "{$year}{$serial}";

        return sprintf('%s-%d-%s-%d', $prefix, $year, $serial, $this->checkDigit($payload));
    }

    public function internationalHawb(?string $destinationCountry): string
    {
        $prefix = $this->hawbPrefix($destinationCountry);
        $year = (int) now()->format('Y');
        $sequence = $this->nextSequence("hawb:{$prefix}", $year);
        $serial = str_pad((string) $sequence, (int) config('tracking.hawb_sequence_width', 3), '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$serial}";
    }

    public function domesticHawb(?string $destinationDistrict = null): string
    {
        $prefix = config('tracking.domestic_hawb_prefix', 'DOMNP');
        $year = (int) now()->format('Y');
        $sequence = $this->nextSequence("hawb:{$prefix}", $year);
        $serial = str_pad((string) $sequence, (int) config('tracking.hawb_sequence_width', 3), '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$serial}";
    }

    public function ecommerceHawb(): string
    {
        $prefix = config('tracking.ecommerce_hawb_prefix', 'ECNP');
        $year = (int) now()->format('Y');
        $sequence = $this->nextSequence("hawb:{$prefix}", $year);
        $serial = str_pad((string) $sequence, (int) config('tracking.hawb_sequence_width', 3), '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$serial}";
    }

    public function isValidTrackingNumber(string $trackingNumber): bool
    {
        if (!preg_match('/^(NPD|NPE|NPI)-(\d{4})-(\d{6,})-(\d)$/', strtoupper(trim($trackingNumber)), $matches)) {
            return false;
        }

        return $this->checkDigit($matches[2] . $matches[3]) === (int) $matches[4];
    }

    private function hawbPrefix(?string $destinationCountry): string
    {
        $country = strtoupper(trim((string) $destinationCountry));

        foreach (config('tracking.international_hawb.regions', []) as $prefix => $countries) {
            if (in_array($country, $countries, true)) {
                return $prefix;
            }
        }

        return config('tracking.international_hawb.default_prefix', 'INNP');
    }

    private function nextSequence(string $namespace, int $year): int
    {
        return DB::transaction(function () use ($namespace, $year) {
            DB::table('number_sequences')->insertOrIgnore([
                'namespace' => $namespace,
                'year' => $year,
                'last_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('number_sequences')
                ->where('namespace', $namespace)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $next = ((int) $row->last_value) + 1;

            DB::table('number_sequences')
                ->where('id', $row->id)
                ->update([
                    'last_value' => $next,
                    'updated_at' => now(),
                ]);

            return $next;
        }, 5);
    }

    private function checkDigit(string $digits): int
    {
        $sum = 0;
        $reversed = strrev($digits);

        foreach (str_split($reversed) as $index => $digit) {
            $value = (int) $digit;

            if ($index % 2 === 0) {
                $value *= 2;
                $value = $value > 9 ? $value - 9 : $value;
            }

            $sum += $value;
        }

        return (10 - ($sum % 10)) % 10;
    }
}
