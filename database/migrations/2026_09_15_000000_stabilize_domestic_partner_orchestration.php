<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('delivery_zones', 'partner_user_id')) {
            Schema::table('delivery_zones', function (Blueprint $table) {
                $table->foreignId('partner_user_id')->nullable()->after('partner_id')->constrained('users')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('delivery_zones', 'province')) {
            Schema::table('delivery_zones', fn (Blueprint $table) => $table->string('province')->nullable()->after('zone_type'));
        }
        if (! Schema::hasColumn('delivery_zones', 'district')) {
            Schema::table('delivery_zones', fn (Blueprint $table) => $table->string('district')->nullable()->after('province'));
        }

        if (! Schema::hasColumn('pickup_requests', 'partner_user_id')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                $table->foreignId('partner_user_id')->nullable()->after('partner_id')->constrained('users')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('pickup_requests', 'shipment_id')) {
            Schema::table('pickup_requests', fn (Blueprint $table) => $table->foreignId('shipment_id')->nullable()->after('order_id')->constrained('shipments')->nullOnDelete());
        }

        if (Schema::hasTable('domestic_shipments') && ! Schema::hasColumn('domestic_shipments', 'partner_user_id')) {
            Schema::table('domestic_shipments', function (Blueprint $table) {
                $table->foreignId('partner_user_id')->nullable()->after('partner_id')->constrained('users')->nullOnDelete();
            });
        }

        $rateColumns = [
            'rate_type', 'approval_status', 'submitted_by', 'approved_by',
            'submitted_at', 'approved_at', 'rejection_reason', 'pickup_charge',
            'origin_handling_charge', 'destination_handling_charge',
            'remote_area_surcharge', 'cod_charge', 'admin_margin_type',
            'admin_margin_value', 'is_default_destination',
        ];

        if (Schema::hasTable('domestic_rates') && collect($rateColumns)->contains(fn ($column) => ! Schema::hasColumn('domestic_rates', $column))) {
            Schema::table('domestic_rates', function (Blueprint $table) {
                if (! Schema::hasColumn('domestic_rates', 'rate_type')) {
                    $table->string('rate_type')->default('door_to_door')->after('service_type');
                }
                if (! Schema::hasColumn('domestic_rates', 'approval_status')) {
                    $table->string('approval_status')->default('approved')->after('is_active');
                }
                if (! Schema::hasColumn('domestic_rates', 'submitted_by')) {
                    $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('domestic_rates', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('domestic_rates', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable();
                }
                if (! Schema::hasColumn('domestic_rates', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }
                if (! Schema::hasColumn('domestic_rates', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable();
                }
                if (! Schema::hasColumn('domestic_rates', 'pickup_charge')) {
                    $table->decimal('pickup_charge', 12, 2)->default(0);
                }
                if (! Schema::hasColumn('domestic_rates', 'origin_handling_charge')) {
                    $table->decimal('origin_handling_charge', 12, 2)->default(0);
                }
                if (! Schema::hasColumn('domestic_rates', 'destination_handling_charge')) {
                    $table->decimal('destination_handling_charge', 12, 2)->default(0);
                }
                if (! Schema::hasColumn('domestic_rates', 'remote_area_surcharge')) {
                    $table->decimal('remote_area_surcharge', 12, 2)->default(0);
                }
                if (! Schema::hasColumn('domestic_rates', 'cod_charge')) {
                    $table->decimal('cod_charge', 12, 2)->default(0);
                }
                if (! Schema::hasColumn('domestic_rates', 'admin_margin_type')) {
                    $table->string('admin_margin_type')->default('percentage');
                }
                if (! Schema::hasColumn('domestic_rates', 'admin_margin_value')) {
                    $table->decimal('admin_margin_value', 12, 2)->default(10);
                }
                if (! Schema::hasColumn('domestic_rates', 'is_default_destination')) {
                    $table->boolean('is_default_destination')->default(false);
                }
            });
        }

        if (! Schema::hasTable('domestic_partner_assignments')) {
            Schema::create('domestic_partner_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('zone_id')->constrained('delivery_zones')->cascadeOnDelete();
                $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
                $table->string('leg_type');
                $table->string('service_type')->default('standard');
                $table->unsignedInteger('priority')->default(1);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('daily_capacity')->nullable();
                $table->time('cutoff_time')->nullable();
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['zone_id', 'partner_id', 'leg_type', 'service_type'], 'domestic_assignment_unique');
                $table->index(['zone_id', 'leg_type', 'service_type', 'is_active'], 'domestic_assignment_lookup');
            });
        }

        if (! Schema::hasTable('shipment_legs')) {
            Schema::create('shipment_legs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('sequence');
                $table->string('leg_type');
                $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('domestic_rate_id')->nullable()->constrained('domestic_rates')->nullOnDelete();
                $table->foreignId('origin_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
                $table->foreignId('destination_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
                $table->string('origin_name')->nullable();
                $table->string('destination_name')->nullable();
                $table->string('status')->default('pending');
                $table->string('assignment_source')->default('default');
                $table->foreignId('selected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('partner_cost', 12, 2)->default(0);
                $table->decimal('markup_amount', 12, 2)->default(0);
                $table->decimal('customer_price', 12, 2)->default(0);
                $table->string('currency', 3)->default('NPR');
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['shipment_id', 'sequence']);
                $table->index(['partner_id', 'status']);
            });
        }

        if (! Schema::hasTable('domestic_rate_events')) {
            Schema::create('domestic_rate_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('domestic_rate_id')->constrained('domestic_rates')->cascadeOnDelete();
                $table->string('event_type');
                $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->json('before')->nullable();
                $table->json('after')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('shipment_leg_events')) {
            Schema::create('shipment_leg_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shipment_leg_id')->constrained('shipment_legs')->cascadeOnDelete();
                $table->string('event_type');
                $table->string('from_status')->nullable();
                $table->string('to_status')->nullable();
                $table->foreignId('from_partner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('to_partner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        $this->backfillCanonicalPartnerIds();
    }

    private function backfillCanonicalPartnerIds(): void
    {
        if (! Schema::hasTable('domestic_partners') || ! Schema::hasTable('users')) {
            return;
        }

        foreach (DB::table('domestic_partners')->select('id', 'email')->orderBy('id')->get() as $legacyPartner) {
            $userId = DB::table('users')
                ->where('email', $legacyPartner->email)
                ->where('user_type', 'partner')
                ->value('id');

            if (! $userId) {
                continue;
            }

            if (Schema::hasColumn('delivery_zones', 'partner_user_id')) {
                DB::table('delivery_zones')->where('partner_id', $legacyPartner->id)->whereNull('partner_user_id')->update(['partner_user_id' => $userId]);
            }
            if (Schema::hasColumn('pickup_requests', 'partner_user_id')) {
                DB::table('pickup_requests')->where('partner_id', $legacyPartner->id)->whereNull('partner_user_id')->update(['partner_user_id' => $userId]);
            }
            if (Schema::hasTable('domestic_shipments') && Schema::hasColumn('domestic_shipments', 'partner_user_id')) {
                DB::table('domestic_shipments')->where('partner_id', $legacyPartner->id)->whereNull('partner_user_id')->update(['partner_user_id' => $userId]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_leg_events');
        Schema::dropIfExists('domestic_rate_events');
        Schema::dropIfExists('shipment_legs');
        Schema::dropIfExists('domestic_partner_assignments');

        // Canonical columns are deliberately retained on rollback because
        // dropping them could orphan live assignments after deployment.
    }
};
