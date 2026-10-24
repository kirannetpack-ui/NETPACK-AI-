<?php

namespace App\Http\Controllers\Domestic;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DomesticRate;
use App\Models\DeliveryZone;
use App\Models\DomesticShipment;
use App\Models\Product;
use App\Models\Order;
use App\Models\PickupRequest;
use App\Models\Shipment;
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
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user) {
                abort(403, 'Unauthorized access. Please login.');
            }
            
            $allowedTypes = ['domestic_admin', 'super_admin', 'admin', 'staff'];
            if (!in_array($user->user_type, $allowedTypes)) {
                abort(403, 'Unauthorized access. You do not have permission to access this service.');
            }
            return $next($request);
        });
    }

    /**
     * Domestic Admin Dashboard - Shows both Domestic and E-commerce stats
     */
    public function dashboard()
    {
        // Domestic Service Stats
        $domesticStats = [
            'total_partners' => User::where('user_type', 'partner')->count(),
            'active_partners' => User::where('user_type', 'partner')
                ->where('verification_status', 'approved')
                ->count(),
            'total_rates' => DomesticRate::count(),
            'active_rates' => DomesticRate::where('is_active', true)->count(),
            'total_zones' => DeliveryZone::count(),
            'total_domestic_shipments' => DomesticShipment::count(),
            'pending_domestic_shipments' => DomesticShipment::where('status', 'pending')->count(),
            'delivered_domestic_shipments' => DomesticShipment::where('status', 'delivered')->count(),
            'total_pickups' => PickupRequest::count(),
            'pending_pickups' => PickupRequest::where('status', 'pending')->count(),
        ];

        // E-commerce Stats
        $ecommerceStats = [
            'total_sellers' => User::where('user_type', 'seller')->count(),
            'active_sellers' => User::where('user_type', 'seller')
                ->where('verification_status', 'approved')
                ->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'total_shipments' => Shipment::where('shipment_type', 'ecommerce')->count(),
            'pending_shipments' => Shipment::where('shipment_type', 'ecommerce')
                ->where('status', 'pending')
                ->count(),
        ];

        // Recent Activities
        $recentDomesticShipments = DomesticShipment::with(['client', 'partner'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentOrders = Order::with(['seller', 'client'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentPickups = PickupRequest::with(['seller', 'assignedRider'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('domestic.admin.dashboard', compact(
            'domesticStats',
            'ecommerceStats',
            'recentDomesticShipments',
            'recentOrders',
            'recentPickups'
        ));
    }

    // =============================================
    // DOMESTIC SERVICES METHODS
    // =============================================

    /**
     * List domestic partners
     */
    public function partners()
    {
        $partners = User::where('user_type', 'partner')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('domestic.admin.partners', compact('partners'));
    }

    /**
     * Show partner details
     */
    public function showPartner($id)
    {
        $partner = User::where('user_type', 'partner')->findOrFail($id);
        
        $stats = [
            'total_shipments' => DomesticShipment::where('partner_id', $id)->count(),
            'delivered_shipments' => DomesticShipment::where('partner_id', $id)
                ->where('status', 'delivered')
                ->count(),
            'total_pickups' => PickupRequest::where('partner_id', $id)->count(),
            'total_rates' => DomesticRate::where('partner_id', $id)->count(),
            'total_zones' => DeliveryZone::where('partner_id', $id)->count(),
        ];

        return view('domestic.admin.partner-details', compact('partner', 'stats'));
    }

    /**
     * List domestic rates
     */
    public function rates()
    {
        $rates = DomesticRate::with(['partner', 'originZone', 'destinationZone'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('domestic.admin.rates', compact('rates'));
    }

    /**
     * List delivery zones
     */
    public function zones()
    {
        $zones = DeliveryZone::with('partner')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('domestic.admin.zones', compact('zones'));
    }

    /**
     * List domestic shipments
     */
    public function shipments(Request $request)
    {
        $query = DomesticShipment::with(['client', 'partner', 'rider']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        $shipments = $query->orderBy('created_at', 'desc')->paginate(20);
        $partners = User::where('user_type', 'partner')->get();

        return view('domestic.admin.shipments', compact('shipments', 'partners'));
    }

    /**
     * Show domestic shipment details
     */
    public function showShipment($id)
    {
        $shipment = DomesticShipment::with(['client', 'partner', 'rider', 'domesticRate'])
            ->findOrFail($id);

        return view('domestic.admin.shipment-details', compact('shipment'));
    }

    /**
     * Update domestic shipment status
     */
    public function updateShipmentStatus(Request $request, $id)
    {
        $shipment = DomesticShipment::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,picked_up,in_transit,out_for_delivery,delivered,failed_delivery,returned,cancelled',
            'notes' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $newStatus = $request->status;
        if ($newStatus !== $shipment->status) {
            $scanService = app(ShipmentScanService::class);
            $eventCode = $scanService->eventCodeForStatus($newStatus);
            abort_unless($eventCode, 422, 'No operational scan event is configured for this status.');
            $scanService->record($shipment, $eventCode, $request->location, $request->notes, $request->user(), 'domestic_admin');
        }

        return redirect()->route('domestic.shipments.show', $id)
            ->with('success', 'Shipment status updated successfully!');
    }

    /**
     * List pickup requests
     */
    public function pickups(Request $request)
    {
        $query = PickupRequest::with(['seller', 'assignedRider', 'partner']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pickups = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('domestic.admin.pickups', compact('pickups'));
    }

    // =============================================
    // E-COMMERCE METHODS
    // =============================================

    /**
     * List sellers
     */
    public function sellers(Request $request)
    {
        $query = User::where('user_type', 'seller');

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        $sellers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('domestic.admin.sellers', compact('sellers'));
    }

    /**
     * Show seller details
     */
    public function showSeller($id)
    {
        $seller = User::where('user_type', 'seller')->findOrFail($id);
        
        $stats = [
            'total_products' => Product::where('user_id', $id)->count(),
            'active_products' => Product::where('user_id', $id)->where('is_active', true)->count(),
            'total_orders' => Order::where('seller_id', $id)->count(),
            'pending_orders' => Order::where('seller_id', $id)->where('status', 'pending')->count(),
            'completed_orders' => Order::where('seller_id', $id)->where('status', 'completed')->count(),
            'total_revenue' => Order::where('seller_id', $id)->where('status', 'completed')->sum('total_amount'),
        ];

        return view('domestic.admin.seller-details', compact('seller', 'stats'));
    }

    /**
     * List products
     */
    public function products(Request $request)
    {
        $query = Product::with('user');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('domestic.admin.products', compact('products'));
    }

    /**
     * List orders
     */
    public function orders(Request $request)
    {
        $query = Order::with(['seller', 'client']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('domestic.admin.orders', compact('orders'));
    }

    /**
     * Show order details
     */
    public function showOrder($id)
    {
        $order = Order::with(['seller', 'client', 'items.product'])
            ->findOrFail($id);

        return view('domestic.admin.order-details', compact('order'));
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $order->update([
            'status' => $request->status,
            'admin_notes' => $request->notes,
        ]);

        return redirect()->route('domestic.orders.show', $id)
            ->with('success', 'Order status updated successfully!');
    }

    /**
     * Reports - Combined Domestic & E-commerce
     */
    public function reports(Request $request)
    {
        $reports = [
            // Domestic Reports
            'domestic_shipments' => DomesticShipment::count(),
            'domestic_revenue' => DomesticShipment::where('status', 'delivered')->sum('total_amount'),
            'domestic_by_service' => DomesticShipment::select('service_type', DB::raw('COUNT(*) as count'))
                ->groupBy('service_type')
                ->get(),
            
            // E-commerce Reports
            'ecommerce_orders' => Order::count(),
            'ecommerce_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'ecommerce_products' => Product::count(),
            'top_products' => Product::withSum(['orders as sales_count' => fn ($query) => $query->whereIn('orders.status', ['completed', 'delivered'])], 'order_items.quantity')->orderByDesc('sales_count')->limit(5)->get(),
            'top_sellers' => User::where('user_type', 'seller')
                ->withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->limit(5)
                ->get(),
        ];

        return view('domestic.admin.reports', compact('reports'));
    }

/**
 * Show create partner form
 */
public function createPartner()
{
    return view('domestic.admin.partner-create');
}

/**
 * Store new partner
 */
public function storePartner(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'phone' => 'required|string|max:20',
        'password' => 'required|string|min:8|confirmed',
        'company_name' => 'nullable|string|max:255',
        'contact_person' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:100',
        'district' => 'nullable|string|max:100',
        'province' => 'nullable|string|max:100',
        'verification_status' => 'nullable|in:pending,approved,suspended',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $partner = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'user_type' => 'partner',
        'role' => 'partner',
        'verification_status' => $request->verification_status ?? 'pending',
        'business_name' => $request->company_name ?? $request->business_name ?? $request->name,
        'city' => $request->city,
        'district' => $request->district,
        'province' => $request->province,
        'registration_completed' => true,
    ]);

    \App\Models\DomesticPartner::updateOrCreate(
        ['email' => $request->email],
        [
            'id' => $partner->id,
            'name' => $request->name,
            'code' => 'PTN-' . strtoupper(substr($request->district ?? 'DOM', 0, 3)) . '-' . rand(100, 999),
            'company_name' => $request->company_name ?? $request->business_name ?? $request->name,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->city ?? ($request->district ?? 'Nepal'),
            'city' => $request->city ?? ($request->district ?? 'Nepal'),
            'district' => $request->district ?? 'Kathmandu',
            'province' => $request->province ?? 'Bagmati Province',
            'is_active' => true,
        ]
    );

    return redirect()->route('domestic.partners')
        ->with('success', 'Partner created successfully!');
}

/**
 * Show create rate form
 */
public function createRate()
{
    $partners = User::where('user_type', 'partner')->get();
    $zones = DeliveryZone::where('is_active', true)->get();
    return view('domestic.admin.rate-create', compact('partners', 'zones'));
}

/**
 * Store new rate
 */
public function storeRate(Request $request)
{
    $validator = Validator::make($request->all(), [
        'partner_id' => 'required|exists:users,id',
        'origin_zone_id' => 'required|exists:delivery_zones,id',
        'destination_zone_id' => 'required|exists:delivery_zones,id',
        'service_type' => 'required|in:flash,same_day,standard,himalayan',
        'weight_from' => 'required|numeric|min:0',
        'weight_to' => 'required|numeric|gt:weight_from',
        'base_rate' => 'required|numeric|min:0',
        'per_kg_rate' => 'required|numeric|min:0',
        'estimated_hours' => 'nullable|integer|min:0',
        'estimated_days' => 'nullable|integer|min:0',
        'effective_from' => 'required|date',
        'effective_to' => 'nullable|date|after:effective_from',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $serviceNames = [
        'flash' => 'FLASH',
        'same_day' => 'SAME DAY',
        'standard' => 'STANDARD',
        'himalayan' => 'HIMALAYAN',
    ];

    DomesticRate::create([
        'partner_id' => $request->partner_id,
        'origin_zone_id' => $request->origin_zone_id,
        'destination_zone_id' => $request->destination_zone_id,
        'service_type' => $request->service_type,
        'service_name' => $serviceNames[$request->service_type],
        'weight_from' => $request->weight_from,
        'weight_to' => $request->weight_to,
        'base_rate' => $request->base_rate,
        'per_kg_rate' => $request->per_kg_rate,
        'estimated_hours' => $request->estimated_hours,
        'estimated_days' => $request->estimated_days,
        'is_active' => true,
        'effective_from' => $request->effective_from,
        'effective_to' => $request->effective_to,
    ]);

    return redirect()->route('domestic.rates')
        ->with('success', 'Rate created successfully!');
}

/**
 * Show create zone form
 */
public function createZone()
{
    $partners = User::where('user_type', 'partner')->get();
    $zoneTypes = ['urban', 'semi_urban', 'rural', 'hilly', 'himalayan'];
    return view('domestic.admin.zone-create', compact('partners', 'zoneTypes'));
}

/**
 * Store new zone
 */
public function storeZone(Request $request)
{
    $validator = Validator::make($request->all(), [
        'partner_id' => 'required|exists:users,id',
        'zone_name' => 'required|string|max:255',
        'zone_type' => 'required|in:urban,semi_urban,rural,hilly,himalayan',
        'districts' => 'nullable',
        'municipalities' => 'nullable|string',
        'wards' => 'nullable|string',
        'description' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $districts = [];
    if (is_array($request->districts)) {
        $districts = array_values(array_filter($request->districts));
    } elseif (is_string($request->districts) && trim($request->districts) !== '') {
        $districts = array_values(array_filter(array_map('trim', explode(',', $request->districts))));
    }

    $partnerId = $request->partner_id;
    $partnerUser = User::find($partnerId);
    if ($partnerUser) {
        $domPartner = \App\Models\DomesticPartner::where('id', $partnerId)
            ->orWhere('email', $partnerUser->email)
            ->first();
        if ($domPartner) {
            $partnerId = $domPartner->id;
        } else {
            $domPartner = \App\Models\DomesticPartner::create([
                'name' => $partnerUser->name,
                'code' => 'PTN-' . strtoupper(substr($partnerUser->district ?? 'DOM', 0, 3)) . '-' . rand(100, 999),
                'company_name' => $partnerUser->business_name ?? $partnerUser->name,
                'email' => $partnerUser->email,
                'password' => $partnerUser->password ?? bcrypt('secret123'),
                'phone' => $partnerUser->phone ?? '9800000000',
                'district' => $partnerUser->district ?? 'Kathmandu',
                'province' => $partnerUser->province ?? 'Bagmati Province',
                'address' => $partnerUser->address ?? 'Nepal',
                'city' => $partnerUser->city ?? 'Kathmandu',
                'is_active' => true,
            ]);
            $partnerId = $domPartner->id;
        }
    }

    DeliveryZone::create([
        'partner_id' => $partnerId,
        'zone_name' => $request->zone_name,
        'zone_code' => strtoupper(substr($request->zone_name, 0, 3)) . '-' . rand(100, 999),
        'zone_type' => $request->zone_type,
        'districts' => $districts,
        'municipalities' => $request->municipalities ? explode(',', $request->municipalities) : [],
        'wards' => $request->wards ? explode(',', $request->wards) : [],
        'description' => $request->description,
        'is_active' => true,
    ]);

    return redirect()->route('domestic.zones')
        ->with('success', 'Zone created successfully!');
}

/**
 * Show edit partner form
 */
public function editPartner($id)
{
    $partner = User::where('user_type', 'partner')->findOrFail($id);
    return view('domestic.admin.partner-edit', compact('partner'));
}

/**
 * Update partner
 */
public function updatePartner(Request $request, $id)
{
    $partner = User::where('user_type', 'partner')->findOrFail($id);

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        'phone' => 'required|string|max:20',
        'company_name' => 'nullable|string|max:255',
        'contact_person' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:100',
        'district' => 'nullable|string|max:100',
        'province' => 'nullable|string|max:100',
        'verification_status' => 'nullable|in:pending,approved,suspended',
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
        'business_name' => $request->company_name ?? $request->business_name ?? $partner->business_name,
        'city' => $request->city,
        'district' => $request->district,
        'province' => $request->province,
        'verification_status' => $request->verification_status ?? 'pending',
    ];

    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $partner->update($data);

    \App\Models\DomesticPartner::where('id', $partner->id)->orWhere('email', $partner->email)->update(array_filter([
        'name' => $request->name,
        'company_name' => $request->company_name ?? $request->business_name,
        'phone' => $request->phone,
        'city' => $request->city,
        'district' => $request->district,
        'province' => $request->province,
    ]));

    return redirect()->route('domestic.partners')
        ->with('success', 'Partner updated successfully!');
}

/**
 * Delete partner
 */
public function deletePartner($id)
{
    $partner = User::where('user_type', 'partner')->findOrFail($id);
    
    // Check if partner has shipments
    $shipmentCount = DomesticShipment::where('partner_id', $id)->count();
    if ($shipmentCount > 0) {
        return redirect()->route('domestic.partners')
            ->with('error', 'Cannot delete partner. They have ' . $shipmentCount . ' shipments.');
    }

    $partner->delete();

    return redirect()->route('domestic.partners')
        ->with('success', 'Partner deleted successfully!');
}

/**
 * Show edit rate form
 */
public function editRate($id)
{
    $rate = DomesticRate::findOrFail($id);
    $partners = User::where('user_type', 'partner')->get();
    $zones = DeliveryZone::where('is_active', true)->get();
    return view('domestic.admin.rate-edit', compact('rate', 'partners', 'zones'));
}

/**
 * Update rate
 */
public function updateRate(Request $request, $id)
{
    $rate = DomesticRate::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'partner_id' => 'required|exists:users,id',
        'origin_zone_id' => 'required|exists:delivery_zones,id',
        'destination_zone_id' => 'required|exists:delivery_zones,id',
        'service_type' => 'required|in:flash,same_day,standard,himalayan',
        'weight_from' => 'required|numeric|min:0',
        'weight_to' => 'required|numeric|gt:weight_from',
        'base_rate' => 'required|numeric|min:0',
        'per_kg_rate' => 'required|numeric|min:0',
        'estimated_hours' => 'nullable|integer|min:0',
        'estimated_days' => 'nullable|integer|min:0',
        'effective_from' => 'required|date',
        'effective_to' => 'nullable|date|after:effective_from',
        'is_active' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $serviceNames = [
        'flash' => 'FLASH',
        'same_day' => 'SAME DAY',
        'standard' => 'STANDARD',
        'himalayan' => 'HIMALAYAN',
    ];

    $rate->update([
        'partner_id' => $request->partner_id,
        'origin_zone_id' => $request->origin_zone_id,
        'destination_zone_id' => $request->destination_zone_id,
        'service_type' => $request->service_type,
        'service_name' => $serviceNames[$request->service_type],
        'weight_from' => $request->weight_from,
        'weight_to' => $request->weight_to,
        'base_rate' => $request->base_rate,
        'per_kg_rate' => $request->per_kg_rate,
        'estimated_hours' => $request->estimated_hours,
        'estimated_days' => $request->estimated_days,
        'is_active' => $request->has('is_active'),
        'effective_from' => $request->effective_from,
        'effective_to' => $request->effective_to,
    ]);

    return redirect()->route('domestic.rates')
        ->with('success', 'Rate updated successfully!');
}

/**
 * Delete rate
 */
public function deleteRate($id)
{
    $rate = DomesticRate::findOrFail($id);
    $rate->delete();

    return redirect()->route('domestic.rates')
        ->with('success', 'Rate deleted successfully!');
}

/**
 * Show edit zone form
 */
public function editZone($id)
{
    $zone = DeliveryZone::findOrFail($id);
    $partners = User::where('user_type', 'partner')->get();
    $zoneTypes = ['urban', 'semi_urban', 'rural', 'hilly', 'himalayan'];
    return view('domestic.admin.zone-edit', compact('zone', 'partners', 'zoneTypes'));
}

/**
 * Update zone
 */
public function updateZone(Request $request, $id)
{
    $zone = DeliveryZone::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'partner_id' => 'required|exists:users,id',
        'zone_name' => 'required|string|max:255',
        'zone_type' => 'required|in:urban,semi_urban,rural,hilly,himalayan',
        'districts' => 'nullable',
        'municipalities' => 'nullable|string',
        'wards' => 'nullable|string',
        'description' => 'nullable|string',
        'is_active' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $districts = [];
    if (is_array($request->districts)) {
        $districts = array_values(array_filter($request->districts));
    } elseif (is_string($request->districts) && trim($request->districts) !== '') {
        $districts = array_values(array_filter(array_map('trim', explode(',', $request->districts))));
    }

    $partnerId = $request->partner_id;
    $partnerUser = User::find($partnerId);
    if ($partnerUser) {
        $domPartner = \App\Models\DomesticPartner::where('id', $partnerId)
            ->orWhere('email', $partnerUser->email)
            ->first();
        if ($domPartner) {
            $partnerId = $domPartner->id;
        }
    }

    $zone->update([
        'partner_id' => $partnerId,
        'zone_name' => $request->zone_name,
        'zone_type' => $request->zone_type,
        'districts' => $districts,
        'municipalities' => $request->municipalities ? explode(',', $request->municipalities) : [],
        'wards' => $request->wards ? explode(',', $request->wards) : [],
        'description' => $request->description,
        'is_active' => $request->has('is_active'),
    ]);

    return redirect()->route('domestic.zones')
        ->with('success', 'Zone updated successfully!');
}

/**
 * Delete zone
 */
public function deleteZone($id)
{
    $zone = DeliveryZone::findOrFail($id);
    $zone->delete();

    return redirect()->route('domestic.zones')
        ->with('success', 'Zone deleted successfully!');
}

}
