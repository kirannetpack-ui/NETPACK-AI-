<?php

namespace App\Http\Controllers\International;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OverseasBaseRate;
use App\Models\MarginRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class RateUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin,international_admin']);
    }

    public function create()
    {
        $partners = User::where('user_type', 'overseas')->get();
        return view('international.admin.rate-upload', compact('partners'));
    }

    public function parse(Request $request)
    {
        $request->validate([
            'rate_file' => 'required|file|mimes:xlsx,xls,csv',
            'overseas_partner_id' => 'required|exists:users,id',
            'service_type' => 'required|string',
            'effective_from' => 'required|date',
        ]);

        $file = $request->file('rate_file');
        $extension = $file->getClientOriginalExtension();
        
        // Parse the file
        $parsedData = $this->smartParseFile($file, $extension);
        
        // Auto-detect column mapping
        $columnMapping = $this->detectColumns($parsedData['headers'] ?? []);
        
        // Group countries by zone if detected
        $groupedData = $this->groupByZone($parsedData['rows'] ?? [], $columnMapping);

        return view('international.admin.rate-preview', [
            'parsedData' => $groupedData,
            'columnMapping' => $columnMapping,
            'partnerId' => $request->overseas_partner_id,
            'serviceType' => $request->service_type,
            'effectiveFrom' => $request->effective_from,
            'effectiveTo' => $request->effective_to,
            'fileName' => $file->getClientOriginalName(),
            'headers' => $parsedData['headers'] ?? [],
        ]);
    }

    private function smartParseFile($file, $extension)
    {
        $data = [];
        $headers = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            // Get headers (first row)
            $headers = array_map('trim', $rows[0]);
            
            // Process data rows
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;
                $data[] = $row;
            }
        } elseif ($extension === 'csv') {
            $reader = new Csv();
            $spreadsheet = $reader->load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            $headers = array_map('trim', $rows[0]);
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;
                $data[] = $row;
            }
        }

        return [
            'headers' => $headers,
            'rows' => $data,
        ];
    }

    private function detectColumns($headers)
    {
        $mapping = [];
        $headerMap = [
            'country' => ['country', 'destination', 'to country', 'to', 'country name', 'country code'],
            'weight_from' => ['weight from', 'min weight', 'from weight', 'weight_min', 'min'],
            'weight_to' => ['weight to', 'max weight', 'to weight', 'weight_max', 'max'],
            'rate' => ['rate', 'rate per kg', 'rate/kg', 'price', 'cost', 'amount'],
            'zone' => ['zone', 'group', 'region', 'area'],
            'countries' => ['countries', 'country list', 'multiple countries'],
        ];

        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            foreach ($headerMap as $field => $keywords) {
                foreach ($keywords as $keyword) {
                    if (strpos($headerLower, $keyword) !== false) {
                        $mapping[$field] = $index;
                        break 2;
                    }
                }
            }
        }

        // Auto-detect if we have simple format (Country, Weight, Rate)
        if (!isset($mapping['weight_from']) && isset($mapping['country']) && isset($mapping['rate'])) {
            // Try to find weight column
            foreach ($headers as $index => $header) {
                $headerLower = strtolower(trim($header));
                if (strpos($headerLower, 'weight') !== false || strpos($headerLower, 'kg') !== false) {
                    $mapping['weight'] = $index;
                    break;
                }
            }
        }

        return $mapping;
    }

    private function groupByZone($rows, $mapping)
    {
        $grouped = [];
        
        foreach ($rows as $row) {
            $rateData = [];
            
            // Handle zone-based grouping
            if (isset($mapping['zone']) && isset($mapping['countries'])) {
                $zone = $row[$mapping['zone']] ?? '';
                $countries = explode(',', $row[$mapping['countries']] ?? '');
                $rate = floatval($row[$mapping['rate']] ?? 0);
                $weightFrom = floatval($row[$mapping['weight_from']] ?? 0);
                $weightTo = floatval($row[$mapping['weight_to']] ?? 1000);
                
                foreach ($countries as $country) {
                    $country = trim($country);
                    if (!empty($country)) {
                        $grouped[] = [
                            'country' => $country,
                            'zone' => $zone,
                            'weight_from' => $weightFrom,
                            'weight_to' => $weightTo,
                            'rate_per_kg' => $rate,
                            'is_zone' => true,
                        ];
                    }
                }
            } 
            // Handle individual country rates
            else if (isset($mapping['country']) && isset($mapping['rate'])) {
                $country = $row[$mapping['country']] ?? '';
                $rate = floatval($row[$mapping['rate']] ?? 0);
                $weightFrom = floatval($row[$mapping['weight_from']] ?? 0);
                $weightTo = floatval($row[$mapping['weight_to']] ?? 1000);
                
                // If weight is in a single column
                if (!isset($mapping['weight_from']) && isset($mapping['weight'])) {
                    $weight = floatval($row[$mapping['weight']] ?? 0);
                    $weightFrom = 0;
                    $weightTo = $weight;
                }
                
                if (!empty($country) && $rate > 0) {
                    $grouped[] = [
                        'country' => $country,
                        'weight_from' => $weightFrom,
                        'weight_to' => $weightTo,
                        'rate_per_kg' => $rate,
                        'is_zone' => false,
                    ];
                }
            }
        }
        
        return $grouped;
    }

    public function import(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:users,id',
            'rates' => 'required|array',
            'service_type' => 'required|string',
            'effective_from' => 'required|date',
        ]);

        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($request->rates as $index => $rateData) {
            try {
                $rateData = json_decode($rateData, true);
                
                OverseasBaseRate::create([
                    'overseas_partner_id' => $request->partner_id,
                    'country_from' => 'Nepal',
                    'country_to' => $rateData['country'],
                    'weight_from' => $rateData['weight_from'] ?? 0,
                    'weight_to' => $rateData['weight_to'] ?? 1000,
                    'rate_per_kg' => $rateData['rate_per_kg'],
                    'minimum_rate' => $rateData['rate_per_kg'] * 0.5, // 50% of rate as minimum
                    'service_type' => $request->service_type,
                    'is_active' => true,
                    'effective_from' => $request->effective_from,
                    'effective_to' => $request->effective_to,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return redirect()->route('international.rates')
            ->with('success', "Successfully imported {$imported} rates. {$failed} failed.")
            ->with('errors', $errors);
    }
}
