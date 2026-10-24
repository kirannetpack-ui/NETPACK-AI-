<?php

namespace App\Console\Commands;

use App\Services\CarrierTrackingSyncService;
use Illuminate\Console\Command;

class SyncCarrierTrackingCommand extends Command
{
    protected $signature = 'tracking:sync-carriers {--shipment= : Specific shipment ID or tracking number to sync}';
    protected $description = 'Synchronize international shipments with global last-mile delivery carriers (FedEx, DHL, UPS, Royal Mail, etc.)';

    public function handle(CarrierTrackingSyncService $syncService): int
    {
        $this->info('Starting Global Carrier Tracking Synchronization...');

        $specific = $this->option('shipment');

        if ($specific) {
            $shipment = \App\Models\Shipment::where('id', $specific)
                ->orWhere('tracking_number', $specific)
                ->first();

            if (!$shipment) {
                $this->error("Shipment '{$specific}' not found.");
                return self::FAILURE;
            }

            $res = $syncService->syncShipment($shipment);
            if ($res['success']) {
                $this->info("Synced shipment {$shipment->tracking_number}: {$res['message']}");
                return self::SUCCESS;
            } else {
                $this->warn("Shipment {$shipment->tracking_number} sync result: {$res['message']}");
                return self::FAILURE;
            }
        }

        $result = $syncService->syncAllActiveShipments();

        $this->info("Checked {$result['total_checked']} active shipments: {$result['updated_count']} updated, {$result['error_count']} errors/unassigned.");

        return self::SUCCESS;
    }
}
