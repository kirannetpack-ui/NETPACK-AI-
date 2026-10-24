<?php

namespace App\Console\Commands;

use App\Models\DomesticShipment;
use App\Models\ManifestBag;
use App\Services\DomesticRouteAutomationService;
use Illuminate\Console\Command;

class AutomateDomesticTrackingCommand extends Command
{
    protected $signature = 'tracking:automate-domestic';
    protected $description = 'Automate domestic Nepal transit SLA monitoring and fleet milestone updates';

    public function handle(DomesticRouteAutomationService $domesticService): int
    {
        $this->info('Evaluating domestic transit pipelines across Nepal...');

        $activeInTransit = DomesticShipment::where('status', 'in_transit')->count();
        $outForDelivery = DomesticShipment::where('status', 'out_for_delivery')->count();

        $this->info("Domestic fleet status: {$activeInTransit} consignments in transit, {$outForDelivery} out for delivery.");

        return self::SUCCESS;
    }
}
