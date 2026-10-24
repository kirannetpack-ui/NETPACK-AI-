<?php

namespace App\Http\Controllers\Domestic;

use App\Http\Controllers\Controller;
use App\Models\Manifest;
use App\Models\ManifestBag;
use App\Models\ManifestShipment;
use App\Models\Shipment;
use App\Models\ProofOfDelivery;
use App\Models\User;
use App\Models\ReminderLog;
use App\Models\ManifestShipmentEvent;
use App\Notifications\ManifestAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ManifestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all manifests
     */
    public function index(Request $request)
    {
        $query = Manifest::with(['creator', 'partner']);

        $this->ensureManifestUser($request->user());
        if ($request->user()->user_type === 'partner') {
            $query->where('partner_id', $request->user()->id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('manifest_number', 'LIKE', "%{$search}%")
                  ->orWhere('origin_city', 'LIKE', "%{$search}%")
                  ->orWhere('destination_city', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $manifests = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_transit' => (clone $query)->where('status', 'in_transit')->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
        ];

        return view('domestic.manifests.index', compact('manifests', 'stats'));
    }

    /**
     * Show form to create a new manifest
     */
    public function create()
    {
        $this->ensureOperationsUser(auth()->user());
        $partners = User::where('user_type', 'partner')
            ->where('verification_status', 'approved')
            ->get();

        // Get shipments that are not yet manifested
        $shipments = Shipment::where('sender_country', 'Nepal')
            ->where('receiver_country', 'Nepal')
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDoesntHave('manifestShipment')
            ->get();

        return view('domestic.manifests.create', compact('partners', 'shipments'));
    }

    /**
     * Store a new manifest
     */
    public function store(Request $request)
    {
        $this->ensureOperationsUser($request->user());

        $data = $request->validate([
            'load_type' => 'required|in:consolidated,direct,express',
            'partner_id' => 'nullable|exists:users,id',
            'origin_city' => 'required|string|max:255',
            'destination_city' => 'required|string|max:255',
            'delivery_type' => 'required|in:door_delivery,pickup,warehouse',
            'payment_status' => 'required|in:pending,paid,cod',
            'bags' => 'required|array|min:1',
            'bags.*.bag_type' => 'required|in:consolidated,direct,express',
            'bags.*.weight' => 'nullable|numeric|min:0',
            'bags.*.shipments' => 'required|array|min:1',
            'bags.*.shipments.*' => 'required|integer|exists:shipments,id',
        ]);

        if ($data['partner_id'] && !User::whereKey($data['partner_id'])->where('user_type', 'partner')->where('verification_status', 'approved')->exists()) {
            return back()->withErrors(['partner_id' => 'Select an approved domestic delivery partner.'])->withInput();
        }

        $shipmentIds = collect($data['bags'])->pluck('shipments')->flatten();
        if ($shipmentIds->duplicates()->isNotEmpty()) {
            return back()->withErrors(['bags' => 'A shipment can be added to a manifest only once.'])->withInput();
        }

        $manifest = DB::transaction(function () use ($data, $shipmentIds, $request) {
            $shipments = Shipment::whereIn('id', $shipmentIds)->lockForUpdate()->get()->keyBy('id');
            if ($shipments->count() !== $shipmentIds->count() || $shipments->contains(fn (Shipment $shipment) => $shipment->sender_country !== 'Nepal' || $shipment->receiver_country !== 'Nepal')) {
                abort(422, 'Only available domestic shipments may be manifested.');
            }

            $alreadyAssigned = ManifestShipment::whereIn('shipment_id', $shipmentIds)
                ->whereNotIn('status', ['delivered', 'forwarded', 'cancelled'])
                ->lockForUpdate()
                ->exists();
            if ($alreadyAssigned) {
                abort(422, 'One or more selected shipments are already assigned to an active manifest.');
            }

            $manifest = Manifest::create([
                'manifest_number' => Manifest::generateManifestNumber(),
                'created_by' => $request->user()->id,
                'partner_id' => $data['partner_id'],
                'load_type' => $data['load_type'],
                'status' => 'pending',
                'origin_city' => $data['origin_city'],
                'destination_city' => $data['destination_city'],
                'current_location' => $data['origin_city'],
            ]);

            $totalWeight = 0;
            foreach ($data['bags'] as $bagData) {
                $bagShipmentIds = collect($bagData['shipments']);
                $weight = (float) ($bagData['weight'] ?? 0);
                $bag = ManifestBag::create([
                    'manifest_id' => $manifest->id,
                    'bag_number' => ManifestBag::generateBagNumber(),
                    'qr_code' => ManifestBag::generateQRCode(),
                    'bag_type' => $bagData['bag_type'],
                    'shipment_count' => $bagShipmentIds->count(),
                    'weight' => $weight,
                    'current_location' => $data['origin_city'],
                ]);
                $totalWeight += $weight;

                foreach ($bagShipmentIds as $shipmentId) {
                    $line = ManifestShipment::create([
                        'manifest_id' => $manifest->id,
                        'bag_id' => $bag->id,
                        'shipment_id' => $shipmentId,
                        'partner_id' => $data['partner_id'],
                        'delivery_type' => $data['delivery_type'],
                        'payment_status' => $data['payment_status'],
                        'status' => 'pending',
                    ]);
                    ManifestShipmentEvent::create([
                        'manifest_shipment_id' => $line->id,
                        'event_type' => 'manifested',
                        'to_status' => 'pending',
                        'to_partner_id' => $data['partner_id'],
                        'performed_by' => $request->user()->id,
                    ]);
                    $manifest->addTrackingLog('manifested', 'Shipment added to the manifest.', $data['origin_city'], $bag->id, $shipmentId);
                }
            }

            $manifest->update([
                'total_bags' => $manifest->bags()->count(),
                'total_shipments' => $shipmentIds->count(),
                'total_weight' => $totalWeight,
            ]);
            $manifest->addTrackingLog('created', 'Manifest created with assigned shipments.', $data['origin_city']);

            return $manifest;
        });

        if ($manifest->partner) {
            $manifest->partner->notify(new ManifestAssignedNotification($manifest));
            app(\App\Services\ReminderService::class)->scheduleManifestReminders($manifest);
        }

        return redirect()->route('domestic.manifests.show', $manifest)
            ->with('success', 'Manifest created and the assigned partner was notified.');
    }

    /**
     * Show manifest details
     */
    public function show($id)
    {
        $manifest = Manifest::with(['creator', 'partner', 'bags', 'bags.shipments', 'shipments.shipment', 'shipments.bag', 'shipments.events', 'trackingLogs.performedBy'])
            ->findOrFail($id);
        $this->ensureCanAccessManifest(request()->user(), $manifest);
        $forwardPartners = User::whereIn('user_type', ['partner', 'overseas'])
            ->where('verification_status', 'approved')->orderBy('name')->get();

        return view('domestic.manifests.show', compact('manifest', 'forwardPartners'));
    }

    /**
     * Show edit manifest form
     */
    public function edit($id)
    {
        $manifest = Manifest::with(['bags', 'bags.shipments'])->findOrFail($id);
        $this->ensureOperationsUser(auth()->user());
        $partners = User::where('user_type', 'partner')->where('verification_status', 'approved')->get();

        return view('domestic.manifests.edit', compact('manifest', 'partners'));
    }

    /**
     * Update manifest
     */
    public function update(Request $request, $id)
    {
        $manifest = Manifest::findOrFail($id);
        $this->ensureOperationsUser($request->user());
        $data = $request->validate([
            'partner_id' => 'nullable|exists:users,id',
            'origin_city' => 'required|string|max:255',
            'destination_city' => 'required|string|max:255',
            'current_location' => 'nullable|string|max:255',
        ]);
        $manifest->update($data);
        $manifest->addTrackingLog('updated', 'Manifest details updated.', $data['current_location'] ?? $manifest->current_location);

        return redirect()->route('domestic.manifests.show', $manifest)
            ->with('success', 'Manifest details updated successfully.');
    }

    /**
     * Scan bag QR code
     */
    public function scanBag(Request $request)
    {
        $data = $request->validate([
            'qr_code' => 'required|string',
            'action' => 'required|in:receive,sort,dispatch',
            'location' => 'nullable|string|max:255',
        ]);
        $bag = ManifestBag::with('manifest')->where('qr_code', $data['qr_code'])->firstOrFail();
        $this->ensureCanAccessManifest($request->user(), $bag->manifest);

        $status = ['receive' => 'scanned', 'sort' => 'sorted', 'dispatch' => 'dispatched'][$data['action']];
        $timestamps = [
            'scanned' => ['scanned_at' => now()],
            'sorted' => ['sorted_at' => now()],
            'dispatched' => ['dispatched_at' => now()],
        ][$status];
        $bag->update(array_merge($timestamps, ['status' => $status, 'current_location' => $data['location'] ?? $bag->current_location]));
        if ($status === 'dispatched') {
            $bag->shipments()->whereNotIn('status', ['delivered', 'forwarded'])->update(['status' => 'dispatched', 'dispatched_at' => now()]);
            $bag->manifest->update(['status' => 'in_transit', 'dispatched_at' => now(), 'current_location' => $data['location'] ?? $bag->current_location]);
        }
        $bag->manifest->addTrackingLog($status, "Bag {$bag->bag_number} marked {$status}.", $data['location'] ?? $bag->current_location, $bag->id);

        return response()->json([
            'success' => true,
            'message' => "Bag marked {$status} successfully."
        ]);
    }

    /**
     * List all PODs with filters
     */
public function pods(Request $request)
{
    $query = ProofOfDelivery::with(['shipment', 'manifest', 'uploadedBy']);
    $this->applyPodVisibility($query, $request->user());

    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->whereHas('shipment', function($q) use ($search) {
            $q->where('tracking_number', 'LIKE', "%{$search}%")
              ->orWhere('receiver_name', 'LIKE', "%{$search}%");
        });
    }

    if ($request->has('status') && $request->status) {
        $query->where('status', $request->status);
    }

    if ($request->has('date_from') && $request->date_from) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->has('date_to') && $request->date_to) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    $pods = $query->orderBy('created_at', 'desc')->paginate(20);

    $stats = [
        'total' => (clone $query)->count(),
        'uploaded' => (clone $query)->where('status', 'uploaded')->count(),
        'verified' => (clone $query)->where('status', 'verified')->count(),
        'pending' => (clone $query)->where('status', 'pending')->count(),
    ];

    return view('domestic.manifests.pods', compact('pods', 'stats'));
}

    /**
     * Show single POD details
     */
    public function showPod($id)
    {
        $pod = ProofOfDelivery::with(['shipment', 'manifest', 'uploadedBy'])
            ->findOrFail($id);
        $this->ensureCanAccessManifest(request()->user(), $pod->manifest);

        return view('domestic.manifests.pod-details', compact('pod'));
    }

    /**
     * Update POD status
     */
    public function updatePodStatus(Request $request, $id)
    {
        abort_unless(in_array($request->user()->user_type, ['super_admin', 'admin', 'domestic_admin'], true), 403);

        $pod = ProofOfDelivery::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,uploaded,verified,rejected',
            'notes' => 'nullable|string',
        ]);

        $pod->update([
            'status' => $request->status,
            'verified_by' => $request->status === 'verified' ? $request->user()->id : null,
            'verified_at' => $request->status === 'verified' ? now() : null,
            'rejection_reason' => $request->status === 'rejected' ? $request->notes : null,
        ]);

        return redirect()->route('domestic.manifests.pods.show', $pod->id)
            ->with('success', "POD status updated to {$request->status} successfully!");
    }

    /**
 * Upload Proof of Delivery
 */
public function uploadPOD(Request $request)
{
    $request->validate([
        'shipment_id' => 'required|exists:shipments,id',
        'manifest_shipment_id' => 'required|exists:manifest_shipments,id',
        'recipient_name' => 'required|string|max:255',
        'delivered_at' => 'nullable|date',
        'delivery_notes' => 'nullable|string',
        'pod_photo' => 'nullable|image|max:5120', // 5MB max
        'pod_file' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        'signature_data' => 'nullable|string|max:1048576',
    ]);

    try {
        DB::beginTransaction();

        // Lock the record and verify that this manifest line belongs to the
        // submitted shipment. This prevents a valid user from attaching POD to
        // an unrelated shipment by altering request IDs.
        $shipment = Shipment::lockForUpdate()->findOrFail($request->shipment_id);
        $manifestShipment = ManifestShipment::with('manifest')->lockForUpdate()->findOrFail($request->manifest_shipment_id);

        if ((int) $manifestShipment->shipment_id !== (int) $shipment->id) {
            abort(422, 'The selected manifest line does not belong to this shipment.');
        }

        $user = $request->user();
        $isOperationsUser = in_array($user->user_type, ['super_admin', 'admin', 'domestic_admin', 'staff'], true);
        $isAssignedPartner = $user->user_type === 'partner'
            && ((int) $manifestShipment->partner_id === (int) $user->id || (int) optional($manifestShipment->manifest)->partner_id === (int) $user->id);

        abort_unless($isOperationsUser || $isAssignedPartner, 403);

        if ($manifestShipment->status === 'delivered' || ProofOfDelivery::where('manifest_shipment_id', $manifestShipment->id)->exists()) {
            abort(422, 'A proof of delivery already exists for this manifest shipment.');
        }

        // Handle file upload
        $podFile = null;
        $podPhoto = null;
        $signaturePath = null;

        if ($request->hasFile('pod_photo')) {
            $file = $request->file('pod_photo');
            $filename = 'pod_photo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pods/photos', $filename, 'private');
            $podPhoto = $path;
        }

        if ($request->hasFile('pod_file')) {
            $file = $request->file('pod_file');
            $filename = 'pod_file_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pods/files', $filename, 'private');
            $podFile = $path;
        }

        // Handle signature (if provided as base64)
        if ($request->signature_data) {
            $signatureData = $request->signature_data;
            if (str_starts_with($signatureData, 'data:image/png;base64,')) {
                // Decode base64 and save
                $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
                $signatureData = str_replace(' ', '+', $signatureData);
                $image = base64_decode($signatureData, true);
                if ($image === false || $image === '') {
                    abort(422, 'The signature image is invalid.');
                }
                
                $filename = 'signature_' . time() . '_' . uniqid() . '.png';
                $path = 'pods/signatures/' . $filename;
                Storage::disk('private')->put($path, $image);
                $signaturePath = $path;
            }
        }

        if (!$podPhoto && !$podFile && !$signaturePath) {
            abort(422, 'Provide a photo, document, or signature as proof of delivery.');
        }

        // Create POD record
        $pod = ProofOfDelivery::create([
            'manifest_shipment_id' => $manifestShipment->id,
            'shipment_id' => $shipment->id,
            'manifest_id' => $manifestShipment->manifest_id,
            'uploaded_by' => auth()->id(),
            'pod_type' => $request->pod_photo ? 'photo' : ($request->pod_file ? 'file' : 'signature'),
            'pod_file' => $podFile,
            'pod_photo' => $podPhoto,
            'recipient_name' => $request->recipient_name,
            'recipient_signature' => $signaturePath ?? null,
            'delivery_notes' => $request->delivery_notes,
            'delivered_at' => $request->delivered_at ?? now(),
            'status' => 'uploaded',
            'metadata' => ['storage_disk' => 'private'],
        ]);

        // Update shipment status
        $shipment->status = 'delivered';
        $shipment->save();

        // Update manifest shipment status
        $manifestShipment->status = 'delivered';
        $manifestShipment->save();

        DB::commit();

        return redirect()->route('domestic.manifests.pods')
            ->with('success', '✅ Proof of Delivery uploaded successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        report($e);
        return back()
            ->with('error', 'The proof of delivery could not be saved. Please check the details and try again.')
            ->withInput();
    }
}

    /**
     * Serve POD artifacts only to authorised operations users or the partner
     * assigned to the manifest. New records live on the private disk; this
     * keeps old records viewable while they are migrated out of public storage.
     */
    public function downloadPodFile(Request $request, $id, $type)
    {
        $pod = ProofOfDelivery::with('manifestShipment.manifest')->findOrFail($id);
        $user = $request->user();
        $isOperationsUser = in_array($user->user_type, ['super_admin', 'admin', 'domestic_admin', 'staff'], true);
        $manifestShipment = $pod->manifestShipment;
        $isAssignedPartner = $user->user_type === 'partner' && $manifestShipment
            && ((int) $manifestShipment->partner_id === (int) $user->id || (int) optional($manifestShipment->manifest)->partner_id === (int) $user->id);

        abort_unless($isOperationsUser || $isAssignedPartner, 403);

        $path = match ($type) {
            'photo' => $pod->pod_photo,
            'file' => $pod->pod_file,
            'signature' => $pod->recipient_signature,
            default => abort(404),
        };

        abort_unless($path, 404);
        $disk = data_get($pod->metadata, 'storage_disk') === 'private' ? 'private' : 'public';
        abort_unless(Storage::disk($disk)->exists($path), 404);

        if (in_array($type, ['photo', 'signature'], true)) {
            return Storage::disk($disk)->response($path, basename($path), [
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            ]);
        }

        return Storage::disk($disk)->download($path, basename($path));
    }

/**
 * Show upload form for POD
 */
public function showUploadForm($shipmentId)
{
    $shipment = Shipment::findOrFail($shipmentId);
    $manifestShipment = ManifestShipment::with('manifest')->where('shipment_id', $shipmentId)->latest()->first();
    
    if (!$manifestShipment) {
        return redirect()->route('domestic.manifests.pods')
            ->with('error', 'No manifest found for this shipment.');
    }
    $this->ensureCanAccessManifest(request()->user(), $manifestShipment->manifest);
    
    return view('domestic.manifests.pod-upload', compact('shipment', 'manifestShipment'));
}

    /** Update an individual manifest line without permitting invalid jumps. */
    public function updateShipmentStatus(Request $request, Manifest $manifest, ManifestShipment $manifestShipment)
    {
        abort_unless((int) $manifestShipment->manifest_id === (int) $manifest->id, 404);
        $this->ensureCanAccessManifest($request->user(), $manifest);
        $data = $request->validate([
            'status' => 'required|in:received,processed,dispatched,delivery_attempted,delivered,exception',
            'notes' => 'nullable|string|max:2000',
            'location' => 'nullable|string|max:255',
        ]);

        $allowed = [
            'pending' => ['received', 'exception'],
            'received' => ['processed', 'exception'],
            'processed' => ['dispatched', 'exception'],
            'dispatched' => ['delivery_attempted', 'delivered', 'exception'],
            'delivery_attempted' => ['delivered', 'exception'],
            'exception' => ['received', 'processed'],
        ];
        abort_unless(in_array($data['status'], $allowed[$manifestShipment->status] ?? [], true), 422, 'This shipment status transition is not allowed.');

        DB::transaction(function () use ($data, $manifest, $manifestShipment, $request) {
            $fromStatus = $manifestShipment->status;
            $attributes = ['status' => $data['status'], 'notes' => $data['notes'] ?? $manifestShipment->notes];
            if ($data['status'] === 'received') $attributes['received_at'] = now();
            if ($data['status'] === 'dispatched') $attributes['dispatched_at'] = now();
            if ($data['status'] === 'delivered') $attributes['delivered_at'] = now();
            $manifestShipment->update($attributes);
            ManifestShipmentEvent::create([
                'manifest_shipment_id' => $manifestShipment->id,
                'event_type' => 'status_changed',
                'from_status' => $fromStatus,
                'to_status' => $data['status'],
                'performed_by' => $request->user()->id,
                'notes' => $data['notes'] ?? null,
            ]);
            $manifest->addTrackingLog($data['status'], "Shipment status changed from {$fromStatus} to {$data['status']}.", $data['location'] ?? $manifest->current_location, $manifestShipment->bag_id, $manifestShipment->shipment_id);
        });

        return back()->with('success', 'Shipment status updated and recorded in the manifest history.');
    }

    /** Forward an undelivered line through a new auditable manifest. */
    public function forwardShipment(Request $request, Manifest $manifest, ManifestShipment $manifestShipment)
    {
        abort_unless((int) $manifestShipment->manifest_id === (int) $manifest->id, 404);
        $this->ensureCanAccessManifest($request->user(), $manifest);
        abort_if(in_array($manifestShipment->status, ['delivered', 'forwarded', 'cancelled'], true), 422, 'This shipment cannot be forwarded.');
        $data = $request->validate([
            'partner_id' => ['required', \Illuminate\Validation\Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('user_type', ['partner', 'overseas'])->where('verification_status', 'approved'))],
            'notes' => 'required|string|max:2000',
        ]);

        $forwardManifest = DB::transaction(function () use ($data, $manifest, $manifestShipment, $request) {
            $lockedLine = ManifestShipment::lockForUpdate()->findOrFail($manifestShipment->id);
            abort_if(in_array($lockedLine->status, ['delivered', 'forwarded', 'cancelled'], true), 422, 'This shipment was already forwarded or completed.');
            $forwardManifest = Manifest::create([
                'manifest_number' => Manifest::generateManifestNumber(),
                'created_by' => $request->user()->id,
                'partner_id' => $data['partner_id'],
                'load_type' => 'forwarded',
                'status' => 'pending',
                'origin_city' => $manifest->current_location ?: $manifest->origin_city,
                'destination_city' => $manifest->destination_city,
                'current_location' => $manifest->current_location,
                'total_bags' => 1,
                'total_shipments' => 1,
            ]);
            $bag = ManifestBag::create([
                'manifest_id' => $forwardManifest->id,
                'bag_number' => ManifestBag::generateBagNumber(),
                'qr_code' => ManifestBag::generateQRCode(),
                'bag_type' => 'forwarded',
                'shipment_count' => 1,
                'weight' => 0,
                'current_location' => $forwardManifest->current_location,
            ]);
            $forwardLine = ManifestShipment::create([
                'manifest_id' => $forwardManifest->id,
                'bag_id' => $bag->id,
                'shipment_id' => $lockedLine->shipment_id,
                'partner_id' => $data['partner_id'],
                'status' => 'pending',
                'delivery_type' => $lockedLine->delivery_type,
                'payment_status' => $lockedLine->payment_status,
                'notes' => $data['notes'],
            ]);
            $previousStatus = $lockedLine->status;
            $lockedLine->update(['status' => 'forwarded', 'notes' => $data['notes']]);
            ManifestShipmentEvent::create([
                'manifest_shipment_id' => $lockedLine->id,
                'event_type' => 'forwarded',
                'from_status' => $previousStatus,
                'to_status' => 'forwarded',
                'from_partner_id' => $lockedLine->partner_id,
                'to_partner_id' => $data['partner_id'],
                'performed_by' => $request->user()->id,
                'notes' => $data['notes'],
                'metadata' => ['forward_manifest_id' => $forwardManifest->id, 'forward_manifest_shipment_id' => $forwardLine->id],
            ]);
            ManifestShipmentEvent::create([
                'manifest_shipment_id' => $forwardLine->id,
                'event_type' => 're_manifested',
                'to_status' => 'pending',
                'from_partner_id' => $lockedLine->partner_id,
                'to_partner_id' => $data['partner_id'],
                'performed_by' => $request->user()->id,
                'notes' => $data['notes'],
                'metadata' => ['source_manifest_id' => $manifest->id, 'source_manifest_shipment_id' => $lockedLine->id],
            ]);
            $manifest->addTrackingLog('forwarded', "Shipment forwarded to a new partner manifest {$forwardManifest->manifest_number}.", $manifest->current_location, $lockedLine->bag_id, $lockedLine->shipment_id);
            $forwardManifest->addTrackingLog('created', "Forward manifest created from {$manifest->manifest_number}.", $forwardManifest->current_location, $bag->id, $lockedLine->shipment_id);
            return $forwardManifest;
        });

        $forwardManifest->partner?->notify(new ManifestAssignedNotification($forwardManifest));
        app(\App\Services\ReminderService::class)->scheduleManifestReminders($forwardManifest);
        return redirect()->route('domestic.manifests.show', $forwardManifest)->with('success', 'Shipment forwarded and the new partner was notified.');
    }

    /**
     * Show Arrival Notice Verification Form for a Domestic Manifest
     */
    public function arrivalNoticeForm($id)
    {
        $manifest = Manifest::with(['bags', 'bags.shipments', 'shipments.shipment', 'shipments.bag', 'partner'])
            ->findOrFail($id);
        $this->ensureCanAccessManifest(request()->user(), $manifest);

        $nepalHubs = [
            'Kathmandu Central Sortation Gateway',
            'Pokhara Regional Depot (Gandaki)',
            'Biratnagar Hub (Koshi)',
            'Birgunj Gateway (Madhesh)',
            'Narayangarh Transit Hub (Chitwan)',
            'Butwal / Bhairahawa Hub (Lumbini)',
            'Nepalgunj Hub (Mid-Western)',
            'Dhangadhi Hub (Sudurpashchim)',
            'Surkhet Hub (Karnali)',
        ];

        $defaultLocation = $manifest->destination_city ? "{$manifest->destination_city} Hub Depot" : 'Kathmandu Central Gateway';
        $operatorName = auth()->user()->name ?? 'Hub Operations Staff';

        return view('domestic.manifests.arrival-notice', compact('manifest', 'nepalHubs', 'defaultLocation', 'operatorName'));
    }

    /**
     * Process Inbound Arrival Notice for Domestic Manifest (Whole vs Partial selection)
     */
    public function processArrivalNotice(Request $request, $id)
    {
        $manifest = Manifest::with(['shipments.shipment'])->findOrFail($id);
        $this->ensureCanAccessManifest($request->user(), $manifest);

        $validated = $request->validate([
            'arrival_mode' => 'required|in:whole,partial',
            'arrived_shipment_ids' => 'nullable|array',
            'non_arrival_remarks' => 'nullable|array',
            'arrival_location' => 'required|string|max:255',
            'arrival_date' => 'required|date',
            'arrival_time' => 'required|string|max:10',
            'operator_name' => 'nullable|string|max:255',
        ]);

        $timestamp = "{$validated['arrival_date']} {$validated['arrival_time']}:00";
        $location = $validated['arrival_location'];
        $operatorName = $validated['operator_name'] ?: ($request->user()->name ?? 'Hub Operations');
        $isWhole = $validated['arrival_mode'] === 'whole';
        $arrivedIds = collect($validated['arrived_shipment_ids'] ?? [])->map(fn($v) => (int)$v)->all();
        $nonArrivalRemarks = $validated['non_arrival_remarks'] ?? [];

        $arrivedCount = 0;
        $missingCount = 0;

        DB::transaction(function () use ($manifest, $isWhole, $arrivedIds, $nonArrivalRemarks, $timestamp, $location, $operatorName, $request, &$arrivedCount, &$missingCount) {
            foreach ($manifest->shipments as $manifestShipment) {
                $shipmentId = (int) $manifestShipment->id;
                $isMarkedArrived = $isWhole || in_array($shipmentId, $arrivedIds, true);

                if ($isMarkedArrived) {
                    $manifestShipment->update([
                        'arrival_status' => 'arrived',
                        'arrived_at' => $timestamp,
                        'arrived_location' => $location,
                        'staff_name' => $operatorName,
                        'status' => 'received',
                        'received_at' => $timestamp,
                    ]);

                    if ($manifestShipment->shipment) {
                        $manifestShipment->shipment->update([
                            'status' => 'in_transit',
                            'current_location' => $location,
                        ]);
                        $manifestShipment->shipment->addTimeline(
                            'arrival_notice',
                            "Consignment arrived at {$location}. Scanned and verified by {$operatorName}.",
                            $location
                        );
                    }

                    ManifestShipmentEvent::create([
                        'manifest_shipment_id' => $manifestShipment->id,
                        'event_type' => 'arrival_notice',
                        'from_status' => $manifestShipment->status,
                        'to_status' => 'received',
                        'performed_by' => $request->user()->id,
                        'notes' => "Arrived at {$location}. Verified by {$operatorName}.",
                        'metadata' => ['location' => $location, 'operator' => $operatorName, 'timestamp' => $timestamp],
                    ]);

                    $arrivedCount++;
                } else {
                    $remark = $nonArrivalRemarks[$shipmentId] ?? 'Package not found during arrival scan.';
                    $manifestShipment->update([
                        'arrival_status' => 'non_arrival',
                        'non_arrival_remarks' => $remark,
                        'notes' => "NON-ARRIVAL: {$remark}",
                    ]);

                    ManifestShipmentEvent::create([
                        'manifest_shipment_id' => $manifestShipment->id,
                        'event_type' => 'exception',
                        'from_status' => $manifestShipment->status,
                        'to_status' => 'exception',
                        'performed_by' => $request->user()->id,
                        'notes' => "Non-arrival exception: {$remark}",
                        'metadata' => ['location' => $location, 'remark' => $remark],
                    ]);

                    if ($manifestShipment->shipment) {
                        $manifestShipment->shipment->addTimeline(
                            'exception',
                            "Arrival Exception at {$location}: {$remark}",
                            $location
                        );
                    }

                    $missingCount++;
                }
            }

            $newStatus = $missingCount > 0 ? 'partially_received' : 'received';
            $manifest->update([
                'status' => $newStatus,
                'received_at' => $timestamp,
                'current_location' => $location,
            ]);

            $manifest->addTrackingLog(
                $newStatus,
                "Arrival Notice processed at {$location}. Arrived: {$arrivedCount} PKG, Missing/Remarks: {$missingCount} PKG.",
                $location
            );
        });

        return redirect()->route('domestic.manifests.show', $manifest)
            ->with('success', "✅ Inbound Arrival Notice recorded! {$arrivedCount} packages confirmed arrived, {$missingCount} exceptions recorded at {$location}.");
    }

    /**
     * Domestic QR / Barcode Scan Desk View
     */
    public function scanDesk()
    {
        $this->ensureManifestUser(request()->user());
        $nepalHubs = [
            'Kathmandu Central Hub',
            'Pokhara Regional Depot',
            'Biratnagar Hub',
            'Birgunj Gateway',
            'Narayangarh Transit Hub',
            'Butwal Hub',
            'Nepalgunj Hub',
            'Dhangadhi Hub',
            'Surkhet Hub',
        ];
        return view('domestic.manifests.scan', compact('nepalHubs'));
    }

    /**
     * Process Scan from Scan Desk (Consignment / HAWB / Bag QR)
     */
    public function processScan(Request $request)
    {
        $this->ensureManifestUser($request->user());

        $request->validate([
            'barcode' => 'required|string',
            'action' => 'required|in:arrival,dispatch,delivery',
            'location' => 'nullable|string|max:255',
            'status_note' => 'nullable|string|max:1000',
        ]);

        $search = trim($request->barcode);
        $location = $request->location ?: 'Kathmandu Central Hub';
        $operatorName = $request->user()->name ?? 'Hub Staff';
        $note = $request->status_note;

        // Try finding Bag first
        $bag = ManifestBag::with('manifest')->where('qr_code', $search)->orWhere('bag_number', $search)->first();
        if ($bag) {
            $status = match($request->action) {
                'arrival' => 'scanned',
                'delivery' => 'sorted',
                default => 'dispatched',
            };
            $bag->update(['status' => $status, 'current_location' => $location]);
            $bag->manifest->addTrackingLog($status, "Bag {$bag->bag_number} scanned ({$status}) at {$location}.", $location, $bag->id);

            // Automated tracking cascade to enclosed shipments
            try {
                $domesticRouteService = app(\App\Services\DomesticRouteAutomationService::class);
                if ($request->action === 'arrival') {
                    $domesticRouteService->handleBagReceived($bag, $bag->manifest, $location, $request->user());
                } elseif ($request->action === 'dispatch') {
                    $domesticRouteService->handleBagDispatched($bag, $bag->manifest, null, $request->user());
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Bag scan domestic cascade error: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'type' => 'bag',
                'message' => "Bag {$bag->bag_number} successfully stamped as {$status} at {$location}!",
                'item' => [
                    'number' => $bag->bag_number,
                    'status' => ucfirst($status),
                    'location' => $location,
                    'count' => $bag->shipment_count,
                    'operator' => $operatorName,
                    'time' => now()->format('Y-m-d H:i:s'),
                ]
            ]);
        }

        // Try finding Domestic Shipment or Shipment
        $shipment = Shipment::where('tracking_number', $search)->orWhere('hawb_number', $search)->first();
        if (!$shipment) {
            $domShipment = \App\Models\DomesticShipment::where('tracking_number', $search)->first();
            if ($domShipment) {
                $statusMap = [
                    'arrival' => 'in_transit',
                    'dispatch' => 'out_for_delivery',
                    'delivery' => 'delivered',
                ];
                $newStatus = $statusMap[$request->action];
                $domRouteService = app(\App\Services\DomesticRouteAutomationService::class);

                if ($request->action === 'dispatch') {
                    $domRouteService->handleRiderOutForDelivery($domShipment, $request->user(), $location);
                } elseif ($request->action === 'delivery') {
                    $domRouteService->handleDeliveryCompleted($domShipment, null, null, $request->user());
                } else {
                    $domShipment->update([
                        'status' => $newStatus,
                    ]);
                    $domShipment->trackingEvents()->create([
                        'status' => $newStatus,
                        'location' => $location,
                        'description' => "Consignment arrived and processed at {$location} by {$operatorName}.",
                        'event_time' => now(),
                    ]);
                    $history = $domShipment->tracking_history ?? [];
                    $history[] = [
                        'event_code' => $newStatus,
                        'status' => $newStatus,
                        'status_label' => \App\Models\DomesticShipment::STATUS_LABELS[$newStatus] ?? ucfirst($newStatus),
                        'description' => "Consignment arrived and processed at {$location} by {$operatorName}.",
                        'location' => $location,
                        'time' => now()->toIso8601String(),
                    ];
                    $domShipment->update(['tracking_history' => $history]);
                }

                return response()->json([
                    'success' => true,
                    'type' => 'shipment',
                    'message' => "Consignment {$domShipment->tracking_number} updated to {$statusMap[$request->action]} at {$location}!",
                    'item' => [
                        'number' => $domShipment->tracking_number,
                        'receiver' => $domShipment->receiver_name,
                        'destination' => $domShipment->receiver_city,
                        'status' => ucfirst($statusMap[$request->action]),
                        'location' => $location,
                        'operator' => $operatorName,
                        'time' => now()->format('Y-m-d H:i:s'),
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "No consignment or bag found for code: {$search}"
            ], 404);
        }

        // Standard shipment update
        $statusMap = [
            'arrival' => 'in_transit',
            'dispatch' => 'out_for_delivery',
            'delivery' => 'delivered',
        ];
        $newStatus = $statusMap[$request->action];
        $shipment->update([
            'status' => $newStatus,
            'current_location' => $location,
        ]);
        $shipment->addTimeline(
            $request->action,
            $note ?: "Consignment processed ({$request->action}) at {$location} by {$operatorName}.",
            $location
        );

        // Update manifest line if attached
        if ($shipment->manifestShipment) {
            $shipment->manifestShipment->update([
                'status' => $request->action === 'arrival' ? 'received' : ($request->action === 'delivery' ? 'delivered' : 'dispatched'),
                'arrived_location' => $location,
                'staff_name' => $operatorName,
            ]);
        }

        return response()->json([
            'success' => true,
            'type' => 'shipment',
            'message' => "Consignment {$shipment->tracking_number} stamped ({$request->action}) successfully!",
            'item' => [
                'number' => $shipment->tracking_number,
                'receiver' => $shipment->receiver_name,
                'destination' => $shipment->receiver_city,
                'status' => ucfirst(str_replace('_', ' ', $newStatus)),
                'location' => $location,
                'operator' => $operatorName,
                'time' => now()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Bulk Re-Manifesting & Hub-to-Hub Forwarding
     */
    public function bulkRemanifest(Request $request)
    {
        $this->ensureOperationsUser($request->user());

        $validated = $request->validate([
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'required|integer|exists:shipments,id',
            'partner_id' => 'required|exists:users,id',
            'origin_city' => 'required|string|max:255',
            'destination_city' => 'required|string|max:255',
            'load_type' => 'required|in:consolidated,express,re_manifested,linehaul',
            'notes' => 'nullable|string|max:1000',
        ]);

        $newManifest = DB::transaction(function () use ($validated, $request) {
            $partner = User::findOrFail($validated['partner_id']);

            $newManifest = Manifest::create([
                'manifest_number' => Manifest::generateManifestNumber(),
                'created_by' => $request->user()->id,
                'partner_id' => $partner->id,
                'load_type' => $validated['load_type'],
                'manifest_type' => 'domestic',
                'status' => 'pending',
                'origin_city' => $validated['origin_city'],
                'destination_city' => $validated['destination_city'],
                'current_location' => $validated['origin_city'],
                'total_bags' => 1,
                'total_shipments' => count($validated['shipment_ids']),
            ]);

            $bag = ManifestBag::create([
                'manifest_id' => $newManifest->id,
                'bag_number' => ManifestBag::generateBagNumber(),
                'qr_code' => ManifestBag::generateQRCode(),
                'bag_type' => 'consolidated',
                'shipment_count' => count($validated['shipment_ids']),
                'weight' => 0,
                'current_location' => $validated['origin_city'],
            ]);

            $totalWeight = 0;
            foreach ($validated['shipment_ids'] as $shipmentId) {
                $shipment = Shipment::lockForUpdate()->find($shipmentId);
                if (!$shipment) continue;

                $weight = (float)($shipment->weight ?? $shipment->actual_weight ?? 1.0);
                $totalWeight += $weight;

                // Close previous manifest line if exists
                $oldLine = ManifestShipment::where('shipment_id', $shipmentId)
                    ->whereNotIn('status', ['delivered', 'forwarded', 'cancelled'])
                    ->latest()
                    ->first();

                if ($oldLine) {
                    $oldLine->update(['status' => 'forwarded']);
                    ManifestShipmentEvent::create([
                        'manifest_shipment_id' => $oldLine->id,
                        'event_type' => 'forwarded',
                        'from_status' => $oldLine->status,
                        'to_status' => 'forwarded',
                        'performed_by' => $request->user()->id,
                        'notes' => "Re-manifested into {$newManifest->manifest_number}.",
                        'metadata' => ['new_manifest_id' => $newManifest->id],
                    ]);
                }

                // Create new line in outward manifest
                $newLine = ManifestShipment::create([
                    'manifest_id' => $newManifest->id,
                    'bag_id' => $bag->id,
                    'shipment_id' => $shipmentId,
                    'partner_id' => $partner->id,
                    'status' => 'pending',
                    'delivery_type' => 'door_delivery',
                    'payment_status' => 'pending',
                    'notes' => $validated['notes'] ?? "Bulk re-manifested to {$validated['destination_city']}",
                ]);

                ManifestShipmentEvent::create([
                    'manifest_shipment_id' => $newLine->id,
                    'event_type' => 're_manifested',
                    'to_status' => 'pending',
                    'to_partner_id' => $partner->id,
                    'performed_by' => $request->user()->id,
                    'notes' => $validated['notes'] ?? "Assigned to {$partner->name} for {$validated['destination_city']} delivery",
                    'metadata' => ['manifest_number' => $newManifest->manifest_number],
                ]);

                $shipment->update(['current_location' => $validated['origin_city']]);
                $shipment->addTimeline(
                    're_manifested',
                    "Consignment re-manifested into {$newManifest->manifest_number} destined for {$validated['destination_city']} via {$partner->name}.",
                    $validated['origin_city']
                );
            }

            $bag->update(['weight' => $totalWeight]);
            $newManifest->update([
                'total_weight' => $totalWeight,
            ]);

            $newManifest->addTrackingLog(
                're_manifested',
                "Bulk re-manifest created from {$validated['origin_city']} to {$validated['destination_city']} with {$newManifest->total_shipments} packages.",
                $validated['origin_city']
            );

            return $newManifest;
        });

        // Notify partner and schedule reminders
        if ($newManifest->partner) {
            $newManifest->partner->notify(new ManifestAssignedNotification($newManifest));
            app(\App\Services\ReminderService::class)->scheduleManifestReminders($newManifest);
        }

        return redirect()->route('domestic.manifests.show', $newManifest)
            ->with('success', "🚀 Re-Manifest {$newManifest->manifest_number} created successfully with {$newManifest->total_shipments} packages destined for {$newManifest->destination_city}!");
    }

    /**
     * Send immediate manual delivery reminder to the partner for this manifest
     */
    public function sendPartnerReminder(Request $request, $id)
    {
        $manifest = Manifest::with('partner')->findOrFail($id);
        $this->ensureOperationsUser($request->user());

        if (!$manifest->partner) {
            return back()->with('error', 'No domestic delivery partner is assigned to this manifest.');
        }

        $note = $request->input('note', "Please review pending deliveries for manifest {$manifest->manifest_number}.");
        app(\App\Services\ReminderService::class)->sendManualManifestReminder($manifest, $note);

        return back()->with('success', "🔔 Delivery SLA reminder successfully dispatched to {$manifest->partner->name}!");
    }

    private function ensureManifestUser(User $user): void
    {
        abort_unless(in_array($user->user_type, ['super_admin', 'admin', 'domestic_admin', 'staff', 'partner'], true), 403);
    }

    private function ensureOperationsUser(User $user): void
    {
        abort_unless(in_array($user->user_type, ['super_admin', 'admin', 'domestic_admin', 'staff'], true), 403);
    }

    private function ensureCanAccessManifest(User $user, ?Manifest $manifest): void
    {
        $this->ensureManifestUser($user);
        if ($user->user_type === 'partner') {
            abort_unless($manifest && (int) $manifest->partner_id === (int) $user->id, 403);
        }
    }

    private function applyPodVisibility($query, User $user): void
    {
        $this->ensureManifestUser($user);
        if ($user->user_type === 'partner') {
            $query->whereHas('manifest', fn ($manifestQuery) => $manifestQuery->where('partner_id', $user->id));
        }
    }


}
