<?php

namespace Tests\Unit;

use App\Services\TrackingNumberService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TrackingNumberServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('namespace', 80);
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('last_value')->default(0);
            $table->timestamps();
            $table->unique(['namespace', 'year']);
        });

        Carbon::setTestNow('2026-08-16 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_service_specific_tracking_numbers_are_sequential_and_valid(): void
    {
        $service = app(TrackingNumberService::class);

        $domesticOne = $service->domestic();
        $domesticTwo = $service->domestic();
        $ecommerce = $service->ecommerce();

        $this->assertMatchesRegularExpression('/^NPD-2026-000001-\d$/', $domesticOne);
        $this->assertMatchesRegularExpression('/^NPD-2026-000002-\d$/', $domesticTwo);
        $this->assertMatchesRegularExpression('/^NPE-2026-000001-\d$/', $ecommerce);
        $this->assertTrue($service->isValidTrackingNumber($domesticOne));
        $this->assertTrue($service->isValidTrackingNumber($ecommerce));
    }

    public function test_hawb_prefix_is_selected_from_destination(): void
    {
        $service = app(TrackingNumberService::class);

        $this->assertSame('USNP-2026-001', $service->internationalHawb('Canada'));
        $this->assertSame('UKNP-2026-001', $service->internationalHawb('United Kingdom'));
        $this->assertSame('EUNP-2026-001', $service->internationalHawb('France'));
        $this->assertSame('AUNP-2026-001', $service->internationalHawb('Australia'));
        $this->assertSame('INNP-2026-001', $service->internationalHawb('Japan'));
    }

    public function test_invalid_check_digit_is_rejected(): void
    {
        $service = app(TrackingNumberService::class);
        $trackingNumber = $service->domestic();
        $invalid = substr($trackingNumber, 0, -1) . (((int) substr($trackingNumber, -1) + 1) % 10);

        $this->assertFalse($service->isValidTrackingNumber($invalid));
    }
}
