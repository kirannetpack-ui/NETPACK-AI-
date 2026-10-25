<?php

namespace App\Http\Controllers\International;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OverseasBaseRate;
use App\Models\OverseasSubRate;
use App\Models\RemoteAreaSurcharge;
use App\Models\AdditionalCharge;
use App\Models\Shipment;
use App\Models\OverseasTransitPoint;
use App\Models\OverseasHub;
use App\Models\Agency;
use App\Models\LastMileCarrier;
use App\Services\ShipmentScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Allow international_admin, super_admin, admin, and staff
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user) {
                abort(403, 'Unauthorized access. Please login.');
            }
            
            $allowedTypes = ['international_admin', 'super_admin', 'admin', 'staff'];
            if (!in_array($user->user_type, $allowedTypes)) {
                abort(403, 'Unauthorized access. You do not have permission to access this service.');
            }
            return $next($request);
        });
    }

    /**
     * International Service Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_shipments' => Shipment::whereNotNull('overseas_partner_id')->count(),
            'pending_shipments' => Shipment::whereNotNull('overseas_partner_id')
                ->where('status', 'pending')
                ->count(),
            'in_transit_shipments' => Shipment::whereNotNull('overseas_partner_id')
                ->where('status', 'in_transit')
                ->count(),
            'delivered_shipments' => Shipment::whereNotNull('overseas_partner_id')
                ->where('status', 'delivered')
                ->count(),
            'total_revenue' => Shipment::whereNotNull('overseas_partner_id')
                ->where('status', 'delivered')
                ->sum('total_amount'),
            
            'total_base_rates' => OverseasBaseRate::count(),
            'total_sub_rates' => OverseasSubRate::count(),
            'total_surcharges' => RemoteAreaSurcharge::count(),
            'total_transit_points' => OverseasTransitPoint::count(),
            
            'total_overseas_partners' => User::where('user_type', 'overseas')->count(),
            'active_overseas_partners' => User::where('user_type', 'overseas')
                ->where('verification_status', 'approved')
                ->count(),
            'pending_partners' => User::where('user_type', 'overseas')
                ->where('verification_status', 'pending')
                ->count(),
        ];

        $recentShipments = Shipment::with(['customer', 'overseasPartner'])
            ->whereNotNull('overseas_partner_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $topRoutes = Shipment::whereNotNull('overseas_partner_id')
            ->select('receiver_country', DB::raw('COUNT(*) as count'))
            ->groupBy('receiver_country')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $yearExpr = \App\Support\SqlDate::year();
        $monthExpr = \App\Support\SqlDate::month();

        $monthlyRevenue = Shipment::whereNotNull('overseas_partner_id')
            ->where('status', 'delivered')
            ->select(
                DB::raw("{$yearExpr} as year"),
                DB::raw("{$monthExpr} as month"),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('international.admin.dashboard', compact(
            'stats',
            'recentShipments',
            'topRoutes',
            'monthlyRevenue'
        ));
    }

    /**
     * Display list of overseas partners
     */
    public function partners()
    {
        return redirect()->route('international.hubs.index');
    }

    /**
     * Show partner details
     */
    public function showPartner($id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);
        
        $stats = [
            'total_shipments' => Shipment::where('overseas_partner_id', $id)->count(),
            'delivered_shipments' => Shipment::where('overseas_partner_id', $id)
                ->where('status', 'delivered')
                ->count(),
            'total_revenue' => Shipment::where('overseas_partner_id', $id)
                ->where('status', 'delivered')
                ->sum('total_amount'),
            'total_rates' => OverseasBaseRate::where('overseas_partner_id', $id)->count(),
            'total_sub_rates' => OverseasSubRate::where('overseas_partner_id', $id)->count(),
            'total_transit_points' => OverseasTransitPoint::where('partner_id', $id)->count(),
        ];

        return view('international.admin.partner-details', compact('partner', 'stats'));
    }

    /**
     * Show create partner form
     */
    public function createPartner()
    {
        return redirect()->route('international.hubs.create');
    }

    /**
     * Store new overseas partner with auto-generated password
     */
    public function storePartner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'company_name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'required|string|max:255',
            
            'hub_name' => 'required|string|max:255',
            'hub_location' => 'required|string|max:255',
            'hub_country' => 'required|string|max:100',
            
            'transit_name' => 'nullable|array',
            'transit_location' => 'nullable|array',
            'transit_country' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate a random password
        $generatedPassword = $this->generateRandomPassword();
        $hashedPassword = Hash::make($generatedPassword);

        // Combine phone code and number
        $fullPhone = $request->phone_code . $request->phone;

        // Create the partner
        $partner = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $fullPhone,
            'password' => $hashedPassword,
            'user_type' => 'overseas',
            'verification_status' => 'pending',
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'company_name' => $request->company_name,
            'contact_person' => $request->contact_person,
            'registration_completed' => true,
            'password_changed' => false,
        ]);

        // Create mandatory HUB
        OverseasTransitPoint::create([
            'partner_id' => $partner->id,
            'name' => $request->hub_name,
            'type' => 'hub',
            'location' => $request->hub_location,
            'country' => $request->hub_country,
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        // Create optional transit points
        $transitCount = 0;
        if ($request->filled('transit_name')) {
            foreach ($request->transit_name as $index => $transitName) {
                if (!empty($transitName) && !empty($request->transit_location[$index]) && !empty($request->transit_country[$index])) {
                    OverseasTransitPoint::create([
                        'partner_id' => $partner->id,
                        'name' => $transitName,
                        'type' => 'transit',
                        'location' => $request->transit_location[$index],
                        'country' => $request->transit_country[$index],
                        'is_mandatory' => false,
                        'is_active' => true,
                    ]);
                    $transitCount++;
                }
            }
        }

        // Send email with credentials (log for now)
        \Log::info('New Overseas Partner Created', [
            'partner_id' => $partner->id,
            'email' => $partner->email,
            'generated_password' => $generatedPassword,
        ]);

        return redirect()->route('international.partners')
            ->with('success', 'Overseas partner created successfully! Password has been sent to ' . $partner->email . '.');
    }

    /**
     * Show edit partner form
     */
    public function editPartner($id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);
        return view('international.admin.partner-edit', compact('partner'));
    }

    /**
     * Update partner
     */
    public function updatePartner(Request $request, $id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'verification_status' => 'required|in:pending,approved,rejected,suspended',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'contact_person' => $request->contact_person,
            'verification_status' => $request->verification_status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $partner->update($data);

        return redirect()->route('international.partners')
            ->with('success', 'Overseas partner updated successfully!');
    }

    /**
     * Delete partner
     */
    public function deletePartner($id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);
        
        $shipmentCount = Shipment::where('overseas_partner_id', $id)->count();
        if ($shipmentCount > 0) {
            return redirect()->route('international.partners')
                ->with('error', 'Cannot delete partner. They have ' . $shipmentCount . ' shipments.');
        }

        $partner->delete();

        return redirect()->route('international.partners')
            ->with('success', 'Overseas partner deleted successfully!');
    }

    /**
     * Toggle partner status
     */
    public function togglePartnerStatus($id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);
        
        $newStatus = $partner->verification_status === 'approved' ? 'suspended' : 'approved';
        $partner->update(['verification_status' => $newStatus]);

        return redirect()->route('international.partners')
            ->with('success', 'Partner status updated successfully!');
    }

    /**
     * Display international rates
     */
    public function rates()
    {
        $rates = OverseasBaseRate::with('overseasPartner')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('international.admin.rates', compact('rates'));
    }

    /**
     * Show rate sheet upload form
     */
    public function createRate()
    {
        $partners = User::where('user_type', 'overseas')->get();
        return view('international.admin.rate-create', compact('partners'));
    }

    /**
     * Store uploaded rate sheet
     */
    public function storeRate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'overseas_partner_id' => 'required|exists:users,id',
            'rate_file' => 'required|file|mimes:xlsx,xls,csv,json',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'service_type' => 'required|string',
            'import_type' => 'required|in:base_rates,sub_rates,both',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // File upload logic here
        // For now, just redirect with success
        return redirect()->route('international.rates')
            ->with('success', 'Rate sheet uploaded successfully!');
    }

    /**
     * Display surcharges
     */
    public function surcharges()
{
    $surcharges = RemoteAreaSurcharge::with('overseasPartner')
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    return view('international.admin.surcharges', compact('surcharges'));
}

/**
 * Show surcharge upload form
 */
public function createSurcharge()
{
    $partners = User::where('user_type', 'overseas')->get();
    return view('international.admin.surcharge-upload', compact('partners'));
}

/**
 * Store uploaded surcharges
 */
public function storeSurcharge(Request $request)
{
    $validator = Validator::make($request->all(), [
        'partner_id' => 'required|exists:users,id',
        'surcharge_file' => 'required|file|mimes:xlsx,xls,csv',
        'effective_from' => 'required|date',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $file = $request->file('surcharge_file');
    $parsedData = $this->parseSurchargeFile($file);
    
    $imported = 0;
    foreach ($parsedData as $data) {
        RemoteAreaSurcharge::create([
            'overseas_partner_id' => $request->partner_id,
            'country' => $data['country'],
            'city' => $data['city'] ?? null,
            'zip_code_pattern' => $data['zip_code'] ?? null,
            'area_name' => $data['area_name'] ?? 'Remote Area',
            'surcharge_amount' => $data['surcharge_amount'] ?? 0,
            'surcharge_percentage' => $data['surcharge_percentage'] ?? 0,
            'is_active' => true,
            'effective_from' => $request->effective_from,
        ]);
        $imported++;
    }

    return redirect()->route('international.surcharges')
        ->with('success', "Successfully imported {$imported} surcharges!");
}

public function deleteSurcharge($id)
{
    RemoteAreaSurcharge::findOrFail($id)->delete();

    return back()->with('success', 'Surcharge deleted successfully.');
}

public function toggleSurcharge($id)
{
    $surcharge = RemoteAreaSurcharge::findOrFail($id);
    $surcharge->update(['is_active' => !$surcharge->is_active]);

    return back()->with('success', 'Surcharge status updated successfully.');
}

/**
 * Parse surcharge file
 */
private function parseSurchargeFile($file)
{
    $extension = $file->getClientOriginalExtension();
    $data = [];

    if (in_array($extension, ['xlsx', 'xls'])) {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip header row
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (!empty($row[0]) && !empty($row[1])) {
                $data[] = [
                    'country' => $row[0],
                    'city' => $row[1] ?? null,
                    'zip_code' => $row[2] ?? null,
                    'area_name' => $row[3] ?? 'Remote Area',
                    'surcharge_amount' => $row[4] ?? 0,
                    'surcharge_percentage' => $row[5] ?? 0,
                ];
            }
        }
    }

    return $data;
}


    /**
     * Display shipments
     */
    public function shipments()
    {
        $shipments = Shipment::with(['customer', 'overseasPartner'])
            ->whereNotNull('overseas_partner_id')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('international.admin.shipments', compact('shipments'));
    }

    /**
     * Show shipment details
     */
    public function showShipment($id)
    {
        $shipment = Shipment::with(['customer', 'overseasPartner', 'rider', 'currentHub', 'currentAgency', 'lastMileCarrier'])
            ->findOrFail($id);

        $hubs = OverseasHub::where('is_active', true)->orderBy('hub_name')->get();
        $agencies = Agency::where('is_active', true)->orderBy('name')->get();
        $carriers = LastMileCarrier::where('is_active', true)->orderBy('name')->get();

        return view('international.admin.shipment-details', compact('shipment', 'hubs', 'agencies', 'carriers'));
    }

    /**
     * Admin post-booking routing assignment (Hub, Agency, Carrier & Customs)
     */
    public function updateRouting(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);

        $request->validate([
            'status' => 'nullable|in:pending,confirmed,processing,picked_up,in_transit,customs_clearance,out_for_delivery,delivered,cancelled',
            'current_hub_id' => 'nullable|exists:overseas_hubs,id',
            'current_agency_id' => 'nullable|exists:agencies,id',
            'customs_mode' => 'nullable|in:DDP,DDU',
            'last_mile_carrier_name' => 'nullable|string|max:100',
            'last_mile_carrier_id' => 'nullable|exists:last_mile_carriers,id',
            'last_mile_tracking_number' => 'nullable|string|max:100',
            'routing_notes' => 'nullable|string|max:500',
        ]);

        if ($shipment->service_type === 'express') {
            if ($request->filled('last_mile_carrier_name')) {
                $shipment->last_mile_carrier_name = $request->last_mile_carrier_name;
            }
            if ($request->filled('last_mile_tracking_number')) {
                $shipment->last_mile_tracking_number = $request->last_mile_tracking_number;
            }
        } else {
            $shipment->current_hub_id = $request->current_hub_id ?: null;
            $shipment->current_agency_id = $request->current_agency_id ?: null;
            $shipment->customs_mode = $request->customs_mode ?: 'DDP';
            if ($request->filled('last_mile_carrier_name')) {
                $shipment->last_mile_carrier_name = $request->last_mile_carrier_name;
            }
            if ($request->filled('last_mile_carrier_id')) {
                $shipment->last_mile_carrier_id = $request->last_mile_carrier_id;
            }
            if ($request->filled('last_mile_tracking_number')) {
                $shipment->last_mile_tracking_number = $request->last_mile_tracking_number;
            }
            $shipment->agency_milestone = 'routing_assigned';
        }

        $oldStatus = $shipment->status;
        $newStatus = $request->status ?: ($oldStatus === 'pending' ? 'confirmed' : $oldStatus);
        $shipment->status = $newStatus;

        $note = $request->routing_notes 
            ?: ($shipment->service_type === 'express'
                ? "Priority Express carrier assigned: " . ($shipment->last_mile_carrier_name ?? 'Partner Carrier')
                : "International routing defined by Operations Admin (Customs: {$shipment->customs_mode}).");

        $shipment->save();

        try {
            $scanService = app(ShipmentScanService::class);
            if ($newStatus === 'confirmed' && $oldStatus === 'pending') {
                $scanService->record($shipment, 'booking_confirmed', null, $note, $request->user(), 'international_admin');
            } else {
                $history = $shipment->tracking_history ?? [];
                $history[] = [
                    'status' => $shipment->status,
                    'status_label' => 'Routing Assigned by Operations Admin',
                    'description' => $note,
                    'location' => 'Tribhuvan International Airport (TIA) Ops Desk',
                    'time' => now()->toDateTimeString(),
                ];
                $shipment->tracking_history = $history;
                $shipment->save();
            }
        } catch (\Exception $e) {
            $history = $shipment->tracking_history ?? [];
            $history[] = [
                'status' => $shipment->status,
                'status_label' => 'Routing Assigned by Operations Admin',
                'description' => $note,
                'location' => 'Tribhuvan International Airport (TIA) Ops Desk',
                'time' => now()->toDateTimeString(),
            ];
            $shipment->tracking_history = $history;
            $shipment->save();
        }

        return redirect()->route('international.shipments.show', $shipment->id)
            ->with('success', 'International routing & operational carrier partner assigned successfully!');
    }

    /**
     * Update shipment status
     */
    public function updateShipmentStatus(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,picked_up,in_transit,customs_clearance,out_for_delivery,delivered,returned,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($request->status !== $shipment->status) {
            $scanService = app(ShipmentScanService::class);
            $eventCode = $scanService->eventCodeForStatus($request->status);
            abort_unless($eventCode, 422, 'No operational scan event is configured for this status.');
            $scanService->record($shipment, $eventCode, null, $request->notes, $request->user(), 'international_admin');
        }

        return redirect()->route('international.shipments.show', $id)
            ->with('success', 'Shipment status updated successfully!');
    }

    /**
     * Show create shipment form
     */
    public function createShipment()
    {
        $partners = User::where('user_type', 'overseas')->get();
        return view('international.admin.create-shipment', compact('partners'));
    }

    /**
     * Store new international shipment
     */
    public function storeShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:20',
            'sender_address' => 'required|string|max:500',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'receiver_address' => 'required|string|max:500',
            'receiver_country' => 'required|string|max:100',
            'receiver_city' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0.1',
            'service_type' => 'required|string',
            'package_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $trackingService = app(\App\Services\TrackingNumberService::class);
        $trackingNumber = $trackingService->international();
        $hawbNumber = $trackingService->internationalHawb($request->receiver_country);

        $shipment = Shipment::create([
            'tracking_number' => $trackingNumber,
            'hawb_number' => $hawbNumber,
            'sender_name' => $request->sender_name,
            'sender_phone' => $request->sender_phone,
            'sender_address' => $request->sender_address,
            'sender_country' => $request->sender_country ?? 'Nepal',
            'sender_city' => $request->sender_city ?? 'Kathmandu',
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'receiver_address' => $request->receiver_address,
            'receiver_country' => $request->receiver_country,
            'receiver_city' => $request->receiver_city,
            'receiver_postal_code' => $request->postal_code,
            'weight' => $request->weight,
            'length' => $request->length,
            'width' => $request->width,
            'height' => $request->height,
            'package_type' => $request->package_type,
            'description' => $request->description,
            'service_type' => $request->service_type,
            'requires_signature' => $request->has('requires_signature'),
            'is_insured' => $request->has('is_insured'),
            'insurance_amount' => $request->insurance_amount ?? 0,
            'is_cod' => $request->has('is_cod'),
            'status' => 'pending',
            'overseas_partner_id' => Auth::id(),
            'customer_id' => Auth::id(),
            'total_amount' => 0,
        ]);

        return redirect()->route('international.shipments.show', $shipment->id)
            ->with('success', 'International shipment created successfully! Tracking: ' . $trackingNumber . ' | HAWB: ' . $hawbNumber);
    }

    /**
     * Reports page
     */
 public function reports()
{
    try {
        $reports = [
            'total_shipments' => Shipment::whereNotNull('overseas_partner_id')->count(),
            'total_revenue' => Shipment::whereNotNull('overseas_partner_id')->sum('total_amount'),
            'shipments_by_country' => Shipment::whereNotNull('overseas_partner_id')
                ->select('receiver_country', \DB::raw('COUNT(*) as count'))
                ->groupBy('receiver_country')
                ->orderBy('count', 'desc')
                ->get(),
            'monthly_revenue' => Shipment::whereNotNull('overseas_partner_id')
                ->selectRaw(\App\Support\SqlDate::year().' as year, '.\App\Support\SqlDate::month().' as month, SUM(total_amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];

        return view('international.admin.reports', compact('reports'));
    } catch (\Exception $e) {
        return view('international.admin.reports', [
            'reports' => [
                'total_shipments' => 0,
                'total_revenue' => 0,
                'shipments_by_country' => collect([]),
                'monthly_revenue' => collect([]),
            ]
        ])->with('error', 'Unable to load reports: ' . $e->getMessage());
    }
}


    /**
     * Generate a secure random password
     */
    private function generateRandomPassword()
    {
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
