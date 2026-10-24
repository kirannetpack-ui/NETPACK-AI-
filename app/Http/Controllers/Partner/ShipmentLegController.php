<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\ShipmentLeg;
use App\Models\ShipmentLegEvent;
use App\Notifications\DomesticPickupAssignedNotification;
use App\Notifications\ShipmentMilestoneNotification;
use App\Services\DomesticPartnerRoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShipmentLegController extends Controller
{
    public function index(Request $request)
    {
        $legs = ShipmentLeg::with(['shipment', 'originZone', 'destinationZone', 'rate'])
            ->where('partner_id', $request->user()->id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()->paginate(25)->withQueryString();

        return view('partner.shipment-legs.index', compact('legs'));
    }

    public function updateStatus(Request $request, ShipmentLeg $leg)
    {
        $this->authorizePartner($request, $leg);
        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'pickup_en_route', 'picked_up', 'received', 'processed', 'dispatched', 'in_transit', 'out_for_delivery', 'delivery_attempted', 'completed', 'exception'])],
            'notes' => 'nullable|string|max:1000',
        ]);

        if (! $leg->canTransitionTo($data['status'])) {
            throw ValidationException::withMessages(['status' => "{$leg->status} cannot move directly to {$data['status']}."]);
        }

        $oldStatus = $leg->status;
        DB::transaction(function () use ($request, $leg, $data, $oldStatus) {
            $timestamps = match ($data['status']) {
                'accepted' => ['accepted_at' => now()],
                'dispatched', 'in_transit' => ['dispatched_at' => now()],
                'received' => ['received_at' => now()],
                'completed' => ['completed_at' => now()],
                default => [],
            };
            $leg->update(array_merge(['status' => $data['status']], $timestamps));
            ShipmentLegEvent::create([
                'shipment_leg_id' => $leg->id,
                'event_type' => 'status_changed',
                'from_status' => $oldStatus,
                'to_status' => $data['status'],
                'from_partner_id' => $leg->partner_id,
                'to_partner_id' => $leg->partner_id,
                'performed_by' => $request->user()->id,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        $timeline = $leg->shipment->tracking_timeline ?? [];
        $timeline[] = ['status' => $data['status'], 'note' => $data['notes'] ?? ucfirst(str_replace('_', ' ', $data['status'])), 'location' => $leg->destination_name, 'timestamp' => now()->toDateTimeString(), 'updated_by' => $request->user()->name];
        $leg->shipment->update(['tracking_timeline' => $timeline]);
        $leg->shipment->customer?->notify(new ShipmentMilestoneNotification([
            'tracking_number' => $leg->shipment->tracking_number,
            'status' => $data['status'],
            'message' => $data['notes'] ?? 'Shipment '.str_replace('_', ' ', $data['status']).'.',
        ]));

        return back()->with('success', 'Shipment leg status updated.');
    }

    public function alternatives(Request $request, ShipmentLeg $leg, DomesticPartnerRoutingService $routing)
    {
        $this->authorizePartner($request, $leg);
        $zoneId = $leg->leg_type === 'delivery' ? $leg->destination_zone_id : $leg->origin_zone_id;

        return response()->json(['partner_ids' => $routing->eligiblePartnerIds($zoneId, $leg->shipment->service_type, $leg->leg_type)]);
    }

    public function reassign(Request $request, ShipmentLeg $leg, DomesticPartnerRoutingService $routing)
    {
        $this->authorizePartner($request, $leg);
        $data = $request->validate(['partner_id' => 'required|integer|different:current_partner_id', 'reason' => 'required|string|min:5|max:1000']);
        abort_unless(in_array($leg->status, ['assigned', 'accepted', 'exception'], true), 422, 'This leg can no longer be forwarded.');
        if ((int) $data['partner_id'] === (int) $leg->partner_id) {
            throw ValidationException::withMessages(['partner_id' => 'Select a different approved partner.']);
        }

        $zoneId = $leg->leg_type === 'delivery' ? $leg->destination_zone_id : $leg->origin_zone_id;
        $choice = $routing->resolve($zoneId, $leg->shipment->service_type, $leg->leg_type, (int) $data['partner_id']);
        $oldPartnerId = $leg->partner_id;
        $oldStatus = $leg->status;

        DB::transaction(function () use ($request, $leg, $choice, $data, $oldPartnerId, $oldStatus) {
            $leg->update(['partner_id' => $choice['partner']->id, 'status' => 'assigned', 'assignment_source' => 'partner_selected', 'selected_by' => $request->user()->id]);
            ShipmentLegEvent::create([
                'shipment_leg_id' => $leg->id,
                'event_type' => 'partner_forwarded',
                'from_status' => $oldStatus,
                'to_status' => 'assigned',
                'from_partner_id' => $oldPartnerId,
                'to_partner_id' => $choice['partner']->id,
                'performed_by' => $request->user()->id,
                'notes' => $data['reason'],
            ]);
        });

        $choice['partner']->notify(new DomesticPickupAssignedNotification($leg->shipment, $leg->fresh()));

        return back()->with('success', 'Shipment leg forwarded to an approved alternative partner.');
    }

    private function authorizePartner(Request $request, ShipmentLeg $leg): void
    {
        abort_unless($leg->partner_id === $request->user()->id, 403);
    }
}
