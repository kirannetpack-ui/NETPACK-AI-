<?php

namespace App\Http\Controllers\International;

use App\Http\Controllers\Controller;
use App\Models\LastMileCarrier;
use App\Models\OverseasHub;
use Illuminate\Http\Request;

class LastMileCarrierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = LastMileCarrier::with('hub');

        if ($request->filled('hub_id')) {
            $query->where('hub_id', $request->hub_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $carriers = $query->orderBy('sort_order', 'asc')->paginate(15)->withQueryString();
        $hubs = OverseasHub::active()->orderBy('sort_order')->get();

        return view('international.last-mile.index', compact('carriers', 'hubs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|alpha_dash|unique:last_mile_carriers,code',
            'hub_id' => 'nullable|exists:overseas_hubs,id',
            'country' => 'nullable|string|max:100',
            'service_mode' => 'nullable|string|max:100',
            'tracking_url_template' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        LastMileCarrier::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'hub_id' => $validated['hub_id'] ?? null,
            'country' => $validated['country'] ?? null,
            'service_mode' => $validated['service_mode'] ?? 'Standard Courier',
            'tracking_url_template' => $validated['tracking_url_template'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'is_active' => $request->has('is_active'),
            'sort_order' => LastMileCarrier::max('sort_order') + 1,
        ]);

        return redirect()->route('international.last-mile.index')
            ->with('success', "Last Mile Carrier '{$validated['name']}' added successfully.");
    }

    public function update(Request $request, $id)
    {
        $carrier = LastMileCarrier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|alpha_dash|unique:last_mile_carriers,code,' . $carrier->id,
            'hub_id' => 'nullable|exists:overseas_hubs,id',
            'country' => 'nullable|string|max:100',
            'service_mode' => 'nullable|string|max:100',
            'tracking_url_template' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        $carrier->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'hub_id' => $validated['hub_id'] ?? null,
            'country' => $validated['country'] ?? null,
            'service_mode' => $validated['service_mode'] ?? 'Standard Courier',
            'tracking_url_template' => $validated['tracking_url_template'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('international.last-mile.index')
            ->with('success', "Last Mile Carrier '{$carrier->name}' updated successfully.");
    }

    public function destroy($id)
    {
        $carrier = LastMileCarrier::findOrFail($id);
        $carrier->delete();

        return redirect()->route('international.last-mile.index')
            ->with('success', "Carrier '{$carrier->name}' removed.");
    }
}
