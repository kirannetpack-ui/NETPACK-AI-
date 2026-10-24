<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Overseas Hubs Enhancement
        if (Schema::hasTable('overseas_hubs')) {
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE overseas_hubs MODIFY partner_id BIGINT UNSIGNED NULL;');
            }
            Schema::table('overseas_hubs', function (Blueprint $table) {
                if (!Schema::hasColumn('overseas_hubs', 'country')) {
                    $table->string('country')->nullable()->after('location');
                }
                if (!Schema::hasColumn('overseas_hubs', 'coverage_countries')) {
                    $table->json('coverage_countries')->nullable()->after('country');
                }
                if (!Schema::hasColumn('overseas_hubs', 'service_routes')) {
                    $table->text('service_routes')->nullable()->after('coverage_countries');
                }
                if (!Schema::hasColumn('overseas_hubs', 'mode_type')) {
                    $table->string('mode_type')->default('Standard')->after('service_routes');
                }
                if (!Schema::hasColumn('overseas_hubs', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_active');
                }
            });
        }

        // 2. Agencies Enhancement
        if (Schema::hasTable('agencies')) {
            Schema::table('agencies', function (Blueprint $table) {
                if (!Schema::hasColumn('agencies', 'hub_id')) {
                    $table->foreignId('hub_id')->nullable()->after('id')->constrained('overseas_hubs')->onDelete('set null');
                }
                if (!Schema::hasColumn('agencies', 'notification_emails')) {
                    $table->json('notification_emails')->nullable()->after('email');
                }
                if (!Schema::hasColumn('agencies', 'manifest_fields')) {
                    $table->json('manifest_fields')->nullable()->after('notification_emails');
                }
                if (!Schema::hasColumn('agencies', 'datasheet_fields')) {
                    $table->json('datasheet_fields')->nullable()->after('manifest_fields');
                }
                if (!Schema::hasColumn('agencies', 'primary_contact')) {
                    $table->string('primary_contact')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('agencies', 'phone_secondary')) {
                    $table->string('phone_secondary')->nullable()->after('primary_contact');
                }
                if (!Schema::hasColumn('agencies', 'operational_notes')) {
                    $table->text('operational_notes')->nullable()->after('datasheet_fields');
                }
            });
        }

        // 3. Last Mile Carriers Table
        if (!Schema::hasTable('last_mile_carriers')) {
            Schema::create('last_mile_carriers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->foreignId('hub_id')->nullable()->constrained('overseas_hubs')->onDelete('set null');
                $table->string('country')->nullable();
                $table->string('service_mode')->nullable(); // DDP, DDU, Standard, Local Courier
                $table->string('tracking_url_template')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['hub_id', 'is_active']);
            });
        }

        // 4. Master Air Waybills (MAWB) Table
        if (!Schema::hasTable('mawbs')) {
            Schema::create('mawbs', function (Blueprint $table) {
                $table->id();
                $table->string('mawb_number')->unique();
                $table->string('airline_name');
                $table->string('airline_code', 10)->nullable(); // e.g. EK, QR, FZ, RA, TK
                $table->string('origin_airport')->default('KTM');
                $table->string('destination_airport')->nullable();
                $table->foreignId('hub_id')->nullable()->constrained('overseas_hubs')->onDelete('set null');
                $table->string('flight_number')->nullable();
                $table->date('flight_date')->nullable();
                $table->string('status')->default('unused'); // unused, assigned, in_transit, cleared, completed
                $table->unsignedBigInteger('assigned_manifest_id')->nullable();
                $table->integer('total_pieces')->default(0);
                $table->decimal('total_weight', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['status', 'hub_id']);
                $table->index('mawb_number');
            });
        }

        // 5. Manifests Table Enhancement
        if (Schema::hasTable('manifests')) {
            Schema::table('manifests', function (Blueprint $table) {
                if (!Schema::hasColumn('manifests', 'mawb_id')) {
                    $table->foreignId('mawb_id')->nullable()->after('partner_id')->constrained('mawbs')->onDelete('set null');
                }
                if (!Schema::hasColumn('manifests', 'mawb_number')) {
                    $table->string('mawb_number')->nullable()->after('mawb_id');
                }
                if (!Schema::hasColumn('manifests', 'hub_id')) {
                    $table->foreignId('hub_id')->nullable()->after('mawb_number')->constrained('overseas_hubs')->onDelete('set null');
                }
                if (!Schema::hasColumn('manifests', 'agency_id')) {
                    $table->foreignId('agency_id')->nullable()->after('hub_id')->constrained('agencies')->onDelete('set null');
                }
                if (!Schema::hasColumn('manifests', 'service_type')) {
                    $table->string('service_type')->default('economy')->after('load_type');
                }
                if (!Schema::hasColumn('manifests', 'manifest_type')) {
                    $table->string('manifest_type')->default('domestic')->after('service_type');
                }
                if (!Schema::hasColumn('manifests', 'flight_number')) {
                    $table->string('flight_number')->nullable()->after('manifest_type');
                }
                if (!Schema::hasColumn('manifests', 'flight_date')) {
                    $table->date('flight_date')->nullable()->after('flight_number');
                }
                if (!Schema::hasColumn('manifests', 'agency_emails_sent_at')) {
                    $table->timestamp('agency_emails_sent_at')->nullable()->after('delivered_at');
                }
                if (!Schema::hasColumn('manifests', 'agency_emails_sent_to')) {
                    $table->text('agency_emails_sent_to')->nullable()->after('agency_emails_sent_at');
                }
            });
        }

        // 6. Manifest Shipments Table Enhancement
        if (Schema::hasTable('manifest_shipments')) {
            Schema::table('manifest_shipments', function (Blueprint $table) {
                if (!Schema::hasColumn('manifest_shipments', 'arrival_status')) {
                    $table->string('arrival_status')->default('pending')->after('status'); // pending, arrived, non_arrival
                }
                if (!Schema::hasColumn('manifest_shipments', 'arrived_at')) {
                    $table->timestamp('arrived_at')->nullable()->after('arrival_status');
                }
                if (!Schema::hasColumn('manifest_shipments', 'arrived_location')) {
                    $table->string('arrived_location')->nullable()->after('arrived_at');
                }
                if (!Schema::hasColumn('manifest_shipments', 'non_arrival_remarks')) {
                    $table->text('non_arrival_remarks')->nullable()->after('arrived_location');
                }
                if (!Schema::hasColumn('manifest_shipments', 'scanned_by_staff_id')) {
                    $table->unsignedBigInteger('scanned_by_staff_id')->nullable()->after('non_arrival_remarks');
                }
                if (!Schema::hasColumn('manifest_shipments', 'staff_name')) {
                    $table->string('staff_name')->nullable()->after('scanned_by_staff_id');
                }
            });
        }

        // 7. Shipments Table Enhancement
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (!Schema::hasColumn('shipments', 'mawb_id')) {
                    $table->unsignedBigInteger('mawb_id')->nullable()->after('current_transit_point_id');
                }
                if (!Schema::hasColumn('shipments', 'mawb_number')) {
                    $table->string('mawb_number')->nullable()->after('mawb_id');
                }
                if (!Schema::hasColumn('shipments', 'last_mile_carrier_id')) {
                    $table->unsignedBigInteger('last_mile_carrier_id')->nullable()->after('mawb_number');
                }
                if (!Schema::hasColumn('shipments', 'last_mile_carrier_name')) {
                    $table->string('last_mile_carrier_name')->nullable()->after('last_mile_carrier_id');
                }
                if (!Schema::hasColumn('shipments', 'last_mile_tracking_number')) {
                    $table->string('last_mile_tracking_number')->nullable()->after('last_mile_carrier_name');
                }
                if (!Schema::hasColumn('shipments', 'customs_mode')) {
                    $table->string('customs_mode')->nullable()->after('last_mile_tracking_number'); // DDP, DDU, Direct
                }
                if (!Schema::hasColumn('shipments', 'agency_milestone')) {
                    $table->string('agency_milestone')->nullable()->after('customs_mode');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                $columns = ['mawb_id', 'mawb_number', 'last_mile_carrier_id', 'last_mile_carrier_name', 'last_mile_tracking_number', 'customs_mode', 'agency_milestone'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('shipments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('manifest_shipments')) {
            Schema::table('manifest_shipments', function (Blueprint $table) {
                $columns = ['arrival_status', 'arrived_at', 'arrived_location', 'non_arrival_remarks', 'scanned_by_staff_id', 'staff_name'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('manifest_shipments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('manifests')) {
            Schema::table('manifests', function (Blueprint $table) {
                $columns = ['mawb_id', 'mawb_number', 'hub_id', 'agency_id', 'service_type', 'manifest_type', 'flight_number', 'flight_date', 'agency_emails_sent_at', 'agency_emails_sent_to'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('manifests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('mawbs');
        Schema::dropIfExists('last_mile_carriers');

        if (Schema::hasTable('agencies')) {
            Schema::table('agencies', function (Blueprint $table) {
                $columns = ['hub_id', 'notification_emails', 'manifest_fields', 'datasheet_fields', 'primary_contact', 'phone_secondary', 'operational_notes'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('agencies', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('overseas_hubs')) {
            Schema::table('overseas_hubs', function (Blueprint $table) {
                $columns = ['country', 'coverage_countries', 'service_routes', 'mode_type', 'sort_order'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('overseas_hubs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
