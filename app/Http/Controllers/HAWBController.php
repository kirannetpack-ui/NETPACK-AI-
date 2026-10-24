<?php

namespace App\Http\Controllers;

use App\Models\DomesticShipment;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentScanService;
use App\Services\TrackingNumberService;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class HAWBController extends Controller
{
    public function __construct(private readonly ShipmentScanService $scanService) {}

    /**
     * Generate HAWB for a shipment
     */
    public function generate($id, $type = 'international')
    {
        $shipment = $this->findShipment($id, $type);
        abort_unless($this->canView(auth()->user(), $shipment), 403);

        if ($shipment instanceof Shipment && empty($shipment->hawb_number)) {
            try {
                $trackingService = app(TrackingNumberService::class);
                if ($shipment->shipment_type === 'international') {
                    $shipment->hawb_number = $trackingService->internationalHawb($shipment->receiver_country);
                } elseif ($shipment->shipment_type === 'ecommerce') {
                    $shipment->hawb_number = $trackingService->ecommerceHawb();
                } else {
                    $shipment->hawb_number = $trackingService->domesticHawb($shipment->receiver_city);
                }
                $shipment->saveQuietly();
            } catch (\Throwable $e) {
                // Ignore silent generation failure
            }
        }

        $isDomestic = $type === 'domestic'
            || $type === 'ecommerce'
            || ($shipment instanceof DomesticShipment)
            || ($shipment instanceof Shipment && in_array($shipment->shipment_type, ['domestic', 'ecommerce'], true));

        return $isDomestic
            ? $this->generateDomesticHAWB($shipment)
            : $this->generateInternationalHAWB($shipment);
    }

    /**
     * Generate International HAWB
     */
    private function generateInternationalHAWB($shipment)
    {
        $qrCode = $this->generateQRCode($shipment->tracking_number);

        return view('hawb.international', compact('shipment', 'qrCode'));
    }

    /**
     * Generate Domestic HAWB
     */
    private function generateDomesticHAWB($shipment)
    {
        $qrCode = $this->generateQRCode($shipment->tracking_number);

        return view('hawb.domestic', compact('shipment', 'qrCode'));
    }

    /**
     * Generate QR Code using Endroid
     */
    /**
     * Generate QR Code using Google Charts API (No package needed)
     */
    private function generateQRCode($trackingNumber)
    {
        $qrCode = new QrCode(
            data: route('tracking.show', $trackingNumber),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 220,
            margin: 10,
        );
        $dataUri = (new PngWriter)->write($qrCode)->getDataUri();

        return sprintf(
            '<img src="%s" alt="Scan to track shipment %s" width="150" height="150">',
            e($dataUri),
            e($trackingNumber),
        );
    }

    /**
     * Fallback QR code generation (simple text-based)
     */
    private function generateFallbackQRCode($url)
    {
        // Simple QR code generation using Google API (fallback)
        $size = 200;

        return 'data:image/svg+xml;base64,'.base64_encode("
            <svg width='{$size}' height='{$size}' xmlns='http://www.w3.org/2000/svg'>
                <rect width='100%' height='100%' fill='white'/>
                <text x='50%' y='50%' font-family='monospace' font-size='14' text-anchor='middle' dominant-baseline='middle' fill='#0d9488'>
                    📱 Scan to Track
                </text>
                <text x='50%' y='65%' font-family='monospace' font-size='10' text-anchor='middle' dominant-baseline='middle' fill='#64748b'>
                    ".substr($url, 0, 30).'...
                </text>
            </svg>
        ');
    }

    /**
     * Download HAWB as PDF
     */
    public function download($id, $type = 'international')
    {
        return $this->generate($id, $type);
    }

    /**
     * Scan QR Code and update status
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'tracking' => ['required', 'string', 'max:50'],
        ]);
        [$shipment, $type] = $this->findByTracking($validated['tracking']);

        if (! $shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found',
            ], 404);
        }

        abort_unless($this->canView($request->user(), $shipment), 403);

        return response()->json([
            'success' => true,
            'shipment' => [
                'id' => $shipment->id,
                'type' => $type,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status,
                'sender_name' => $shipment->sender_name,
                'receiver_name' => $shipment->receiver_name,
                'receiver_city' => $shipment->receiver_city,
                'service_type' => $shipment->service_type,
                'service' => config('tracking.services.'.$shipment->service_type, config('tracking.services.default')),
                'available_events' => $this->scanService->availableEvents($shipment),
            ],
            'current_status' => $shipment->status,
            'tracking_url' => route('tracking.show', $shipment->tracking_number),
        ]);
    }

    /**
     * Update status via QR scan
     */
    public function updateFromScan(Request $request)
    {
        $validated = $request->validate([
            'tracking' => ['required', 'string', 'max:50'],
            'event_code' => ['nullable', 'required_without:status', 'string', 'max:80'],
            'status' => ['nullable', 'required_without:event_code', 'in:pending,confirmed,processing,picked_up,in_transit,customs_clearance,out_for_delivery,delivered,failed_delivery,returned,cancelled'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        [$shipment] = $this->findByTracking($validated['tracking']);
        if (! $shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found'], 404);
        }

        abort_unless($this->canUpdate($request->user(), $shipment), 403);
        $eventCode = $validated['event_code'] ?? $this->scanService->eventCodeForStatus($validated['status']);
        abort_unless($eventCode, 422, 'No operational scan event is configured for this status.');

        $shipment = $this->scanService->record(
            $shipment,
            $eventCode,
            $validated['location'] ?? null,
            $validated['notes'] ?? null,
            $request->user(),
            'qr',
        );

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'shipment' => [
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status,
                'available_events' => $this->scanService->availableEvents($shipment),
            ],
        ]);
    }

    /**
     * Show HAWB in print popup
     */
    public function printPopup($id, $type = 'international')
    {
        $shipment = $this->findShipment($id, $type);
        abort_unless($this->canView(auth()->user(), $shipment), 403);

        if ($shipment instanceof Shipment && empty($shipment->hawb_number)) {
            try {
                $trackingService = app(TrackingNumberService::class);
                if ($shipment->shipment_type === 'international') {
                    $shipment->hawb_number = $trackingService->internationalHawb($shipment->receiver_country);
                } elseif ($shipment->shipment_type === 'ecommerce') {
                    $shipment->hawb_number = $trackingService->ecommerceHawb();
                } else {
                    $shipment->hawb_number = $trackingService->domesticHawb($shipment->receiver_city);
                }
                $shipment->saveQuietly();
            } catch (\Throwable $e) {
                // Ignore silent generation failure
            }
        }

        $qrCode = $this->generateQRCode($shipment->tracking_number);

        if ($shipment instanceof DomesticShipment || ($shipment instanceof Shipment && in_array($shipment->shipment_type, ['domestic', 'ecommerce'], true))) {
            $type = $shipment->shipment_type ?? 'domestic';
        }

        return view('hawb.print-popup', compact('shipment', 'qrCode', 'type'));
    }

    /**
     * Universal Public HAWB View & Print for Tracking Shipments
     */
    public function publicPrint(string $identifier, Request $request)
    {
        [$shipment, $type] = $this->findByTracking($identifier);

        if (! $shipment) {
            abort(404, "Consignment with tracking or HAWB number '{$identifier}' was not found.");
        }
        abort_unless($this->canView($request->user(), $shipment), 403);

        // Auto-assign regional HAWB number if empty on any consignment
        if ($shipment instanceof Shipment && empty($shipment->hawb_number)) {
            try {
                $trackingService = app(TrackingNumberService::class);
                if ($shipment->shipment_type === 'international') {
                    $shipment->hawb_number = $trackingService->internationalHawb($shipment->receiver_country);
                } elseif ($shipment->shipment_type === 'ecommerce') {
                    $shipment->hawb_number = $trackingService->ecommerceHawb();
                } else {
                    $shipment->hawb_number = $trackingService->domesticHawb($shipment->receiver_city);
                }
                $shipment->saveQuietly();
            } catch (\Throwable $e) {
                // Ignore silent generation failure
            }
        }

        $isDomestic = $type === 'domestic'
            || $type === 'ecommerce'
            || ($shipment instanceof DomesticShipment)
            || ($shipment instanceof Shipment && in_array($shipment->shipment_type, ['domestic', 'ecommerce'], true));

        $qrCode = $this->generateQRCode($shipment->tracking_number);

        // Single-slip popup format if requested
        if ($request->query('format') === 'popup') {
            $type = $isDomestic ? ($shipment->shipment_type ?? 'domestic') : 'international';

            return view('hawb.print-popup', compact('shipment', 'qrCode', 'type'));
        }

        return $isDomestic
            ? view('hawb.domestic', compact('shipment', 'qrCode'))
            : view('hawb.international', compact('shipment', 'qrCode'));
    }

    /**
     * Public Single-Slip HAWB Print Format
     */
    public function publicPrintPopup(string $identifier, Request $request)
    {
        $request->merge(['format' => 'popup']);

        return $this->publicPrint($identifier, $request);
    }

    /**
     * Public HAWB Download
     */
    public function publicDownload(string $identifier, Request $request)
    {
        return $this->publicPrint($identifier, $request);
    }

    private function findShipment($id, string &$type = 'international')
    {
        if ($type === 'domestic' || $type === 'ecommerce') {
            $shipment = DomesticShipment::with(['client', 'partner'])->find($id);
            if ($shipment) {
                return $shipment;
            }

            $shipment = Shipment::with(['customer', 'seller', 'rider', 'overseasPartner'])->find($id);
            if ($shipment) {
                if (! in_array($shipment->shipment_type, ['domestic', 'ecommerce'], true)) {
                    $type = 'international';
                }

                return $shipment;
            }

            abort(404, 'Shipment not found');
        }

        $shipment = Shipment::with(['customer', 'seller', 'rider', 'overseasPartner'])->find($id);
        if ($shipment) {
            if (in_array($shipment->shipment_type, ['domestic', 'ecommerce'], true)) {
                $type = $shipment->shipment_type;
            }

            return $shipment;
        }

        $shipment = DomesticShipment::with(['client', 'partner'])->find($id);
        if ($shipment) {
            $type = 'domestic';

            return $shipment;
        }

        abort(404, 'Shipment not found');
    }

    private function findByTracking(string $trackingNumber): array
    {
        $trackingNumber = trim($trackingNumber);

        // 1. Direct Shipment lookup (by tracking, HAWB, or last-mile carrier)
        $shipment = Shipment::with(['customer', 'seller', 'rider', 'overseasPartner'])
            ->where('tracking_number', $trackingNumber)
            ->orWhere('hawb_number', $trackingNumber)
            ->orWhere('last_mile_tracking_number', $trackingNumber)
            ->first();

        if ($shipment) {
            $type = in_array($shipment->shipment_type, ['domestic', 'ecommerce'], true) ? $shipment->shipment_type : 'international';

            return [$shipment, $type];
        }

        // 2. DomesticShipment lookup
        $domestic = DomesticShipment::with(['client', 'partner'])
            ->where('tracking_number', $trackingNumber)
            ->first();

        if ($domestic) {
            return [$domestic, 'domestic'];
        }

        // 3. Fallback: Lookup by numeric ID
        if (ctype_digit($trackingNumber)) {
            $shipment = Shipment::with(['customer', 'seller', 'rider', 'overseasPartner'])->find($trackingNumber);
            if ($shipment) {
                $type = $shipment->shipment_type === 'domestic' ? 'domestic' : 'international';

                return [$shipment, $type];
            }

            $domestic = DomesticShipment::with(['client', 'partner'])->find($trackingNumber);
            if ($domestic) {
                return [$domestic, 'domestic'];
            }
        }

        return [null, null];
    }

    private function canView(?User $user, $shipment): bool
    {
        // Public tracking routes and public print views are globally accessible
        if (request()->routeIs('tracking.hawb.*') || request()->has('public')) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if (in_array($user->user_type, ['super_admin', 'admin', 'staff', 'domestic_admin', 'international_admin'], true)) {
            return true;
        }

        if ($shipment instanceof DomesticShipment) {
            return (int) $shipment->client_id === (int) $user->id
                || (int) $shipment->partner_id === (int) $user->id;
        }

        return in_array((int) $user->id, array_filter([
            (int) $shipment->customer_id,
            (int) $shipment->seller_id,
            (int) $shipment->rider_id,
            (int) $shipment->overseas_partner_id,
        ]), true);
    }

    private function canUpdate(User $user, $shipment): bool
    {
        if (in_array($user->user_type, ['super_admin', 'admin', 'staff'], true)) {
            return true;
        }

        if ($shipment instanceof DomesticShipment) {
            return $user->user_type === 'domestic_admin'
                || ($user->user_type === 'partner' && (int) $shipment->partner_id === (int) $user->id);
        }

        return $user->user_type === 'international_admin'
            || ($user->user_type === 'overseas' && (int) $shipment->overseas_partner_id === (int) $user->id)
            || ($user->user_type === 'rider' && (int) $shipment->rider_id === (int) $user->id);
    }
}
