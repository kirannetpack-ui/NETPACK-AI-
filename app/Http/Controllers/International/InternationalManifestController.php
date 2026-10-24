<?php

namespace App\Http\Controllers\International;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\LastMileCarrier;
use App\Models\MAWB;
use App\Models\Manifest;
use App\Models\OverseasHub;
use App\Services\InternationalManifestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternationalManifestController extends Controller
{
    public function __construct(private readonly InternationalManifestService $manifestService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Manifest::international()->with(['mawb', 'hub', 'agency', 'creator', 'shipments']);

        if ($request->filled('hub_id')) {
            $query->where('hub_id', $request->hub_id);
        }

        if ($request->filled('agency_id')) {
            $query->where('agency_id', $request->agency_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('manifest_number', 'like', "%{$search}%")
                  ->orWhere('mawb_number', 'like', "%{$search}%")
                  ->orWhere('destination_city', 'like', "%{$search}%");
            });
        }

        $manifests = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total' => Manifest::international()->count(),
            'pending' => Manifest::international()->where('status', 'pending')->count(),
            'in_transit' => Manifest::international()->where('status', 'in_transit')->count(),
            'received' => Manifest::international()->where('status', 'received')->count(),
        ];

        $hubs = OverseasHub::active()->orderBy('sort_order')->get();
        $agencies = Agency::where('is_active', true)->get();

        return view('international.manifests.index', compact('manifests', 'stats', 'hubs', 'agencies'));
    }

    public function create(Request $request)
    {
        $hubId = $request->get('hub_id');
        $agencyId = $request->get('agency_id');
        $serviceType = $request->get('service_type');

        $eligibleShipments = $this->manifestService->getEligibleShipments($hubId, $agencyId, $serviceType);
        $unusedMawbs = $this->manifestService->getUnusedMawbs($hubId);

        $hubs = OverseasHub::active()->with('agencies')->orderBy('sort_order')->get();
        $agencies = Agency::where('is_active', true)->get();
        $carriers = LastMileCarrier::active()->orderBy('sort_order')->get();

        return view('international.manifests.create', compact(
            'eligibleShipments',
            'unusedMawbs',
            'hubs',
            'agencies',
            'carriers',
            'hubId',
            'agencyId',
            'serviceType'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hub_id' => 'required|exists:overseas_hubs,id',
            'agency_id' => 'nullable|exists:agencies,id',
            'mawb_id' => 'required|exists:mawbs,id',
            'service_type' => 'required|in:express,economy',
            'flight_number' => 'nullable|string|max:50',
            'flight_date' => 'nullable|date',
            'last_mile_carrier_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'required|exists:shipments,id',
        ]);

        $manifest = $this->manifestService->createManifest($validated, $validated['shipment_ids'], Auth::user());

        return redirect()->route('international.manifests.show', $manifest->id)
            ->with('success', "International Manifest {$manifest->manifest_number} created automatically and bound to MAWB #{$manifest->mawb_number}!");
    }

    public function show($id)
    {
        $manifest = Manifest::international()
            ->with(['mawb', 'hub', 'agency', 'creator', 'shipments.shipment.customer', 'trackingLogs'])
            ->findOrFail($id);

        return view('international.manifests.show', compact('manifest'));
    }

    /**
     * Display or export the comprehensive Data Sheet
     */
    public function dataSheet($id, Request $request)
    {
        $manifest = Manifest::international()
            ->with(['mawb', 'hub', 'agency', 'shipments.shipment'])
            ->findOrFail($id);

        $dataSheetRows = $this->manifestService->generateDataSheet($manifest);

        if ($request->get('export') === 'csv') {
            return $this->exportDataSheetCsv($manifest, $dataSheetRows);
        }

        return view('international.manifests.data-sheet', compact('manifest', 'dataSheetRows'));
    }

    /**
     * 1-Click Dispatch to Agency Pre-defined Emails
     */
    public function sendAgencyEmail(Request $request, $id)
    {
        $manifest = Manifest::international()->findOrFail($id);

        $result = $this->manifestService->dispatchToAgencyEmails($manifest, $request->input('custom_notes'));

        return redirect()->route('international.manifests.show', $manifest->id)
            ->with('success', "Manifest and Data Sheet transmitted successfully to " . count($result['sent_to']) . " pre-defined agency email(s): " . implode(', ', $result['sent_to']));
    }

    private function exportDataSheetCsv(Manifest $manifest, array $rows)
    {
        $filename = "DataSheet_{$manifest->manifest_number}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            
            if (!empty($rows)) {
                // Header row
                fputcsv($handle, array_keys($rows[0]));

                foreach ($rows as $row) {
                    fputcsv($handle, array_values($row));
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
