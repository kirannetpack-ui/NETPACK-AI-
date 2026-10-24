<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OverseasRate;
use App\Models\RemoteAreaSurcharge;
use App\Models\AdditionalCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class RateSheetController extends Controller
{
    public function index()
    {
        $rates = OverseasRate::with('overseasPartner')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.rates.index', compact('rates'));
    }

    public function create()
    {
        $partners = \App\Models\User::where('user_type', 'overseas')->get();
        return view('admin.rates.create', compact('partners'));
    }

    public function surcharges()
    {
        $surcharges = RemoteAreaSurcharge::with('overseasPartner')
            ->latest()
            ->paginate(25);

        return view('international.admin.surcharges', compact('surcharges'));
    }

    public function charges()
    {
        return response()->json([
            'data' => AdditionalCharge::with('overseasPartner')->latest()->paginate(25),
        ]);
    }

    public function storeCharge(Request $request)
    {
        $validated = $request->validate([
            'overseas_partner_id' => ['required', 'exists:users,id'],
            'charge_name' => ['required', 'string', 'max:255'],
            'charge_type' => ['required', 'in:percentage,fixed,per_kg'],
            'charge_value' => ['required', 'numeric', 'min:0'],
            'applicable_to' => ['required', 'in:all,specific_countries,specific_services'],
            'country_codes' => ['nullable', 'array'],
            'country_codes.*' => ['string', 'max:3'],
            'service_types' => ['nullable', 'array'],
            'service_types.*' => ['string', 'max:50'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        AdditionalCharge::create($validated);

        return back()->with('success', 'Additional charge created successfully.');
    }

    public function destroy($id)
    {
        OverseasRate::findOrFail($id)->delete();

        return back()->with('success', 'Rate deleted successfully.');
    }

    public function toggle($id)
    {
        $rate = OverseasRate::findOrFail($id);
        $rate->update(['is_active' => !$rate->is_active]);

        return response()->json(['is_active' => $rate->is_active]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'overseas_partner_id' => 'required|exists:users,id',
            'rate_file' => 'required|file|mimes:xlsx,xls,csv,json',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'service_type' => 'required|string',
        ]);

        $file = $request->file('rate_file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('rate_sheets', $fileName, 'public');

        // Parse the file
        $parsedData = $this->parseRateSheet($file, $request->overseas_partner_id);

        // Store rates in database
        foreach ($parsedData as $rate) {
            OverseasRate::create([
                'overseas_partner_id' => $request->overseas_partner_id,
                'country_from' => $rate['country_from'] ?? 'Nepal',
                'country_to' => $rate['country_to'],
                'city_from' => $rate['city_from'] ?? null,
                'city_to' => $rate['city_to'] ?? null,
                'weight_from' => $rate['weight_from'] ?? 0,
                'weight_to' => $rate['weight_to'] ?? 1000,
                'rate_per_kg' => $rate['rate_per_kg'],
                'minimum_rate' => $rate['minimum_rate'] ?? 0,
                'service_type' => $request->service_type,
                'transit_time' => $rate['transit_time'] ?? null,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'is_active' => true,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
            ]);
        }

        return redirect()->route('admin.rates.index')
            ->with('success', 'Rate sheet uploaded and parsed successfully! ' . count($parsedData) . ' rates added.');
    }

    public function uploadRemoteSurcharges(Request $request)
    {
        $request->validate([
            'overseas_partner_id' => 'required|exists:users,id',
            'surcharge_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('surcharge_file');
        $parsedData = $this->parseSurchargeSheet($file);

        foreach ($parsedData as $surcharge) {
            RemoteAreaSurcharge::create([
                'overseas_partner_id' => $request->overseas_partner_id,
                'country' => $surcharge['country'],
                'city' => $surcharge['city'] ?? null,
                'zip_code_pattern' => $surcharge['zip_code_pattern'],
                'area_name' => $surcharge['area_name'],
                'surcharge_amount' => $surcharge['surcharge_amount'] ?? 0,
                'surcharge_percentage' => $surcharge['surcharge_percentage'] ?? 0,
                'is_active' => true,
                'effective_from' => now(),
            ]);
        }

        return redirect()->route('admin.rates.index')
            ->with('success', 'Remote area surcharges uploaded successfully!');
    }

    private function parseRateSheet($file, $partnerId)
    {
        $extension = $file->getClientOriginalExtension();
        $data = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Skip header row
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (!empty($row[0]) && !empty($row[1])) {
                    $data[] = [
                        'country_to' => $row[0],
                        'city_to' => $row[1] ?? null,
                        'weight_from' => $row[2] ?? 0,
                        'weight_to' => $row[3] ?? 1000,
                        'rate_per_kg' => $row[4] ?? 0,
                        'minimum_rate' => $row[5] ?? 0,
                        'transit_time' => $row[6] ?? null,
                    ];
                }
            }
        } elseif ($extension === 'csv') {
            $reader = new Csv();
            $spreadsheet = $reader->load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (!empty($row[0]) && !empty($row[1])) {
                    $data[] = [
                        'country_to' => $row[0],
                        'city_to' => $row[1] ?? null,
                        'weight_from' => $row[2] ?? 0,
                        'weight_to' => $row[3] ?? 1000,
                        'rate_per_kg' => $row[4] ?? 0,
                        'minimum_rate' => $row[5] ?? 0,
                        'transit_time' => $row[6] ?? null,
                    ];
                }
            }
        } elseif ($extension === 'json') {
            $jsonData = json_decode(file_get_contents($file->getPathname()), true);
            foreach ($jsonData as $item) {
                $data[] = [
                    'country_to' => $item['country_to'] ?? $item['country'] ?? '',
                    'city_to' => $item['city_to'] ?? $item['city'] ?? null,
                    'weight_from' => $item['weight_from'] ?? 0,
                    'weight_to' => $item['weight_to'] ?? 1000,
                    'rate_per_kg' => $item['rate_per_kg'] ?? $item['rate'] ?? 0,
                    'minimum_rate' => $item['minimum_rate'] ?? $item['min_rate'] ?? 0,
                    'transit_time' => $item['transit_time'] ?? null,
                ];
            }
        }

        return $data;
    }

    private function parseSurchargeSheet($file)
    {
        $extension = $file->getClientOriginalExtension();
        $data = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (!empty($row[0]) && !empty($row[1])) {
                    $data[] = [
                        'country' => $row[0],
                        'city' => $row[1] ?? null,
                        'zip_code_pattern' => $row[2] ?? '',
                        'area_name' => $row[3] ?? 'Remote Area',
                        'surcharge_amount' => $row[4] ?? 0,
                        'surcharge_percentage' => $row[5] ?? 0,
                    ];
                }
            }
        }

        return $data;
    }

    public function calculateInternationalRate(Request $request)
    {
        $request->validate([
            'country_from' => 'required|string',
            'country_to' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'zip_code' => 'nullable|string',
            'overseas_partner_id' => 'required|exists:users,id',
        ]);

        // Get base rate
        $rate = OverseasRate::active()
            ->byCountry($request->country_from, $request->country_to)
            ->byWeight($request->weight)
            ->where('overseas_partner_id', $request->overseas_partner_id)
            ->first();

        if (!$rate) {
            return response()->json(['error' => 'No rate found for the specified route'], 404);
        }

        $baseAmount = max($rate->rate_per_kg * $request->weight, $rate->minimum_rate);

        // Check for remote area surcharge
        $surcharge = RemoteAreaSurcharge::checkSurcharge(
            $request->country_to,
            $request->zip_code,
            $request->overseas_partner_id
        );

        $surchargeAmount = 0;
        if ($surcharge) {
            $surchargeAmount = $surcharge->surcharge_amount ?? 
                              ($baseAmount * ($surcharge->surcharge_percentage / 100));
        }

        // Calculate additional charges
        $additionalCharges = AdditionalCharge::active()
            ->byCountry($request->country_to)
            ->where('overseas_partner_id', $request->overseas_partner_id)
            ->get();

        $additionalTotal = 0;
        foreach ($additionalCharges as $charge) {
            $additionalTotal += $charge->calculateCharge($baseAmount, $request->weight);
        }

        $totalAmount = $baseAmount + $surchargeAmount + $additionalTotal;

        return response()->json([
            'base_rate' => $baseAmount,
            'surcharge' => $surchargeAmount,
            'surcharge_details' => $surcharge ? [
                'area_name' => $surcharge->area_name,
                'country' => $surcharge->country,
                'zip_code' => $request->zip_code,
            ] : null,
            'additional_charges' => $additionalTotal,
            'total' => $totalAmount,
            'breakdown' => [
                'rate_per_kg' => $rate->rate_per_kg,
                'weight' => $request->weight,
                'minimum_rate' => $rate->minimum_rate,
            ],
        ]);
    }
}
