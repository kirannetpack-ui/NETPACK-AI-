<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogisticsServiceController extends Controller
{
    /**
     * Display a listing of the services and transit time SLAs.
     */
    public function index(Request $request)
    {
        $query = LogisticsService::query();

        // Category filter
        $category = $request->get('category', 'all');
        if (in_array($category, ['domestic', 'international', 'ecommerce'])) {
            $query->where('category', $category);
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        // Search query
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query->orderBy('sort_order', 'asc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(15)
                          ->withQueryString();

        // Category counts for quick filtering pills
        $categoryCounts = [
            'all' => LogisticsService::count(),
            'domestic' => LogisticsService::where('category', 'domestic')->count(),
            'ecommerce' => LogisticsService::where('category', 'ecommerce')->count(),
            'international' => LogisticsService::where('category', 'international')->count(),
        ];

        return view('admin.services.index', compact('services', 'categoryCounts', 'category'));
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|alpha_dash|unique:logistics_services,code',
            'category' => 'required|in:domestic,international,ecommerce',
            'transit_time_hours' => 'required|numeric|min:0.25|max:2160',
            'transit_time_days' => 'nullable|numeric|min:0',
            'reminder_intervals' => 'nullable|string',
            'base_rate' => 'nullable|numeric|min:0',
            'per_kg_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-compute days if not explicitly provided
        $hours = (float) $validated['transit_time_hours'];
        $days = !empty($validated['transit_time_days']) ? (float) $validated['transit_time_days'] : round($hours / 24, 2);

        // Parse reminder intervals (e.g. "50, 75, 90")
        $intervals = $this->parseIntervals($request->input('reminder_intervals'));

        LogisticsService::create([
            'name' => $validated['name'],
            'code' => Str::slug($validated['code'], '_'),
            'category' => $validated['category'],
            'transit_time_hours' => $hours,
            'transit_time_days' => $days,
            'reminder_intervals' => $intervals,
            'base_rate' => $validated['base_rate'] ?? 0,
            'per_kg_rate' => $validated['per_kg_rate'] ?? 0,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            'sort_order' => LogisticsService::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.services.index', ['category' => $validated['category']])
            ->with('success', "Logistics Service '{$validated['name']}' created with {$hours}h transit SLA.");
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, $id)
    {
        $service = LogisticsService::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|alpha_dash|unique:logistics_services,code,' . $service->id,
            'category' => 'required|in:domestic,international,ecommerce',
            'transit_time_hours' => 'required|numeric|min:0.25|max:2160',
            'transit_time_days' => 'nullable|numeric|min:0',
            'reminder_intervals' => 'nullable|string',
            'base_rate' => 'nullable|numeric|min:0',
            'per_kg_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $hours = (float) $validated['transit_time_hours'];
        $days = !empty($validated['transit_time_days']) ? (float) $validated['transit_time_days'] : round($hours / 24, 2);
        $intervals = $this->parseIntervals($request->input('reminder_intervals'));

        $service->update([
            'name' => $validated['name'],
            'code' => Str::slug($validated['code'], '_'),
            'category' => $validated['category'],
            'transit_time_hours' => $hours,
            'transit_time_days' => $days,
            'reminder_intervals' => $intervals,
            'base_rate' => $validated['base_rate'] ?? 0,
            'per_kg_rate' => $validated['per_kg_rate'] ?? 0,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $service->is_active,
        ]);

        return redirect()->route('admin.services.index', ['category' => $service->category])
            ->with('success', "Service '{$service->name}' updated successfully (Transit SLA: {$hours}h).");
    }

    /**
     * Remove the specified service.
     */
    public function destroy($id)
    {
        $service = LogisticsService::findOrFail($id);
        $serviceName = $service->name;
        $serviceCategory = $service->category;

        $service->delete();

        return redirect()->route('admin.services.index', ['category' => $serviceCategory])
            ->with('success', "Service '{$serviceName}' deleted successfully.");
    }

    /**
     * Toggle active status.
     */
    public function toggle($id)
    {
        $service = LogisticsService::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();

        $statusStr = $service->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Service '{$service->name}' has been {$statusStr}.");
    }

    /**
     * Helper to parse comma-separated intervals string into array of integers.
     */
    protected function parseIntervals($intervalsInput): array
    {
        if (is_array($intervalsInput)) {
            return array_values(array_filter(array_map('intval', $intervalsInput)));
        }

        if (is_string($intervalsInput) && trim($intervalsInput) !== '') {
            $parts = explode(',', $intervalsInput);
            $parsed = [];
            foreach ($parts as $part) {
                $val = (int) trim($part);
                if ($val > 0 && $val < 100) {
                    $parsed[] = $val;
                }
            }
            if (!empty($parsed)) {
                sort($parsed);
                return array_values(array_unique($parsed));
            }
        }

        return [50, 75, 90];
    }
}
