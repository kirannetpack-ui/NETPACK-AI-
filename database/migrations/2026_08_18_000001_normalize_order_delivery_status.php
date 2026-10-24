<?php

use App\Services\TrackingNumberService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The original e-commerce enum only allowed storefront states such as
        // "shipped" and "completed", while the rider workflow writes assigned,
        // picked_up, in_transit, out_for_delivery and delivered. A bounded
        // string lets both legacy imports and the operational workflow coexist.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 40)->default('pending')->change();
        });

        DB::table('orders')
            ->where(fn ($query) => $query->whereNull('tracking_number')->orWhere('tracking_number', ''))
            ->orderBy('id')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    DB::table('orders')->where('id', $order->id)->update([
                        'tracking_number' => app(TrackingNumberService::class)->ecommerce(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Do not narrow this column back to the legacy enum: doing so could
        // discard valid delivery statuses recorded after this migration.
    }
};
