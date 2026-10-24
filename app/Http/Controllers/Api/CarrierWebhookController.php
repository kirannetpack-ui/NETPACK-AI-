<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CarrierTrackingSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CarrierWebhookController extends Controller
{
    public function __construct(private readonly CarrierTrackingSyncService $syncService) {}

    /**
     * Inbound webhook endpoint for global carriers (FedEx, DHL, UPS, AfterShip/17Track format)
     */
    public function handle(Request $request, string $carrier): JsonResponse
    {
        $carrier = strtolower($carrier);
        $secret = config("carrier_webhooks.secrets.{$carrier}");
        $timestamp = (string) $request->header('X-Netpack-Timestamp', '');
        $signature = (string) $request->header('X-Netpack-Signature', '');
        if (! is_string($secret) || strlen($secret) < 32) {
            return response()->json(['success' => false, 'message' => 'Carrier webhook is not configured.'], 503);
        }
        if (! ctype_digit($timestamp)
            || abs(time() - (int) $timestamp) > config('carrier_webhooks.tolerance_seconds', 300)
            || ! hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret), $signature)) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook signature.'], 401);
        }

        $request->validate([
            'tracking_number' => 'required|string|max:100',
            'status' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'timestamp' => 'required|date|before_or_equal:now',
        ]);

        $replayKey = 'carrier-webhook:'.hash('sha256', $carrier.'.'.$signature);
        if (! Cache::add($replayKey, true, 600)) {
            return response()->json(['success' => true, 'duplicate' => true]);
        }

        $payload = $request->all();

        try {
            $result = $this->syncService->handleInboundWebhook($carrier, $payload);
        } catch (\Throwable $exception) {
            Cache::forget($replayKey);
            throw $exception;
        }

        if (! $result['success']) {
            Cache::forget($replayKey);

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Carrier tracking telemetry processed and synchronized.',
            'data' => $result,
        ]);
    }
}
