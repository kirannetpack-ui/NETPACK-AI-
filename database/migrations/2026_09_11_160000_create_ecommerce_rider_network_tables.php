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
        // 1. Rider Profiles (Extends User with KYC, Vehicle, Affiliation, and COD limits)
        if (!Schema::hasTable('rider_profiles')) {
            Schema::create('rider_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->string('rider_code', 50)->unique();
                $table->string('full_name');
                $table->string('mobile', 30);
                $table->string('email')->nullable();
                $table->date('dob')->nullable();
                $table->enum('gender', ['male', 'female', 'other'])->nullable();
                $table->string('emergency_contact', 50)->nullable();
                
                // Address Info
                $table->string('address')->nullable();
                $table->string('province')->nullable();
                $table->string('district')->nullable();
                $table->string('municipality')->nullable();
                $table->string('ward', 10)->nullable();
                
                // KYC & Identity
                $table->string('citizenship_number', 50)->nullable();
                $table->string('citizenship_front_path')->nullable();
                $table->string('citizenship_back_path')->nullable();
                $table->string('profile_photo_path')->nullable();
                $table->string('selfie_photo_path')->nullable();
                $table->enum('verification_status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending');
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('rejection_reason')->nullable();

                // Vehicle Info
                $table->enum('vehicle_type', ['motorcycle', 'scooter', 'bicycle', 'car', 'van', 'other'])->default('motorcycle');
                $table->string('vehicle_number', 50)->nullable();
                $table->string('vehicle_registration_doc_path')->nullable();
                $table->string('driving_license_number', 50)->nullable();
                $table->string('driving_license_doc_path')->nullable();
                $table->date('license_expiry_date')->nullable();

                // Informational Affiliation (Informational only, zero external API dependency)
                $table->boolean('has_other_platform_affiliation')->default(false);
                $table->string('affiliation', 50)->default('none'); // none, pathao, indrive, parcel, courier_co, freelance, other
                $table->string('affiliation_reference_id')->nullable();
                $table->text('affiliation_notes')->nullable();

                // Service Capacity & Availability
                $table->enum('availability_status', ['online', 'offline', 'busy'])->default('offline');
                $table->decimal('service_radius_km', 8, 2)->default(10.00);
                $table->decimal('max_carrying_weight', 8, 2)->default(15.00);
                $table->integer('max_active_packages')->default(5);
                $table->integer('current_active_packages')->default(0);
                $table->decimal('current_latitude', 10, 7)->nullable();
                $table->decimal('current_longitude', 10, 7)->nullable();
                $table->timestamp('last_location_updated_at')->nullable();

                // COD Security & Limits
                $table->enum('cod_level', ['level_0', 'level_1', 'level_2', 'level_3', 'level_4'])->default('level_0');
                $table->decimal('cod_limit', 12, 2)->default(0.00);
                $table->decimal('current_outstanding_cod', 12, 2)->default(0.00);

                // Performance, Trust & Ratings
                $table->integer('trust_score')->default(100);
                $table->enum('badge_status', ['new', 'verified', 'trusted', 'preferred', 'suspended'])->default('new');
                $table->decimal('rating', 3, 2)->default(5.00);
                $table->integer('total_ratings_count')->default(0);
                $table->integer('total_completed_deliveries')->default(0);
                $table->integer('total_failed_deliveries')->default(0);

                // Remittance / Payout Info
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_account_name')->nullable();
                $table->string('banking_qr_path')->nullable();
                $table->string('esewa_id')->nullable();
                $table->string('khalti_id')->nullable();

                // Legal Agreement
                $table->boolean('agreement_accepted')->default(true);
                $table->timestamp('agreement_accepted_at')->nullable();

                $table->timestamps();
            });
        }

        // 2. Rider Service Areas (District/Ward Coverage Mapping)
        if (!Schema::hasTable('rider_service_areas')) {
            Schema::create('rider_service_areas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rider_profile_id')->constrained('rider_profiles')->onDelete('cascade');
                $table->string('province')->nullable();
                $table->string('district');
                $table->string('municipality')->nullable();
                $table->string('ward', 10)->nullable();
                $table->string('area_name')->nullable();
                $table->decimal('service_radius_km', 8, 2)->default(10.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Rider Rate Rules (Formula-based transparent earnings)
        if (!Schema::hasTable('rider_rate_rules')) {
            Schema::create('rider_rate_rules', function (Blueprint $table) {
                $table->id();
                $table->string('zone_name')->default('Kathmandu Valley');
                $table->string('vehicle_type')->default('motorcycle');
                $table->decimal('base_distance_km', 8, 2)->default(3.00);
                $table->decimal('base_rate', 10, 2)->default(80.00);
                $table->decimal('additional_km_rate', 10, 2)->default(15.00);
                $table->decimal('weight_surcharge_per_kg', 10, 2)->default(10.00);
                $table->decimal('cod_handling_fee', 10, 2)->default(10.00);
                $table->decimal('return_fee', 10, 2)->default(60.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Shipment Assignments (Multi-Leg and Local Direct Chain of Custody)
        if (!Schema::hasTable('shipment_assignments')) {
            Schema::create('shipment_assignments', function (Blueprint $table) {
                $table->id();
                $table->string('master_awb', 50)->index();
                $table->foreignId('shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
                $table->foreignId('domestic_shipment_id')->nullable()->constrained('domestic_shipments')->onDelete('set null');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
                
                // Assignment Route Type & Provider
                $table->enum('assignment_type', [
                    'local_direct',      // Direct Seller -> Customer via Rider
                    'pickup_first_mile', // Leg 1: Seller -> Hub via Rider
                    'line_haul',         // Leg 2: Hub -> Hub via Domestic Partner
                    'last_mile',         // Leg 3: Hub -> Customer via Rider
                    'return_rto'         // Customer -> Hub / Seller
                ])->default('local_direct');
                $table->enum('provider_type', ['rider', 'domestic_partner', 'internal'])->default('rider');
                $table->foreignId('rider_profile_id')->nullable()->constrained('rider_profiles')->onDelete('set null');
                $table->foreignId('partner_id')->nullable()->constrained('domestic_partners')->onDelete('set null');
                $table->integer('sequence')->default(1);

                // Addresses & Contacts
                $table->string('pickup_name');
                $table->string('pickup_phone', 30);
                $table->string('pickup_address');
                $table->decimal('pickup_lat', 10, 7)->nullable();
                $table->decimal('pickup_lng', 10, 7)->nullable();
                
                $table->string('delivery_name');
                $table->string('delivery_phone', 30);
                $table->string('delivery_address');
                $table->decimal('delivery_lat', 10, 7)->nullable();
                $table->decimal('delivery_lng', 10, 7)->nullable();

                $table->decimal('distance_km', 8, 2)->default(5.00);
                $table->decimal('parcel_weight', 8, 2)->default(1.00);

                // Financials
                $table->decimal('provider_fee', 10, 2)->default(0.00);
                $table->decimal('cod_amount', 10, 2)->default(0.00);

                // Execution Status
                $table->enum('status', [
                    'offered',
                    'assigned',
                    'accepted',
                    'arrived_pickup',
                    'picked_up',
                    'in_transit',
                    'arrived_destination',
                    'completed',
                    'failed',
                    'cancelled'
                ])->default('assigned');

                // Chain of Custody & OTP Verification
                $table->string('current_custody')->default('Seller');
                $table->string('pickup_otp', 6)->nullable();
                $table->timestamp('pickup_otp_verified_at')->nullable();
                $table->string('delivery_otp', 6)->nullable();
                $table->timestamp('delivery_otp_verified_at')->nullable();

                // Proof of Delivery (POD)
                $table->string('pod_recipient_name')->nullable();
                $table->string('pod_photo_path')->nullable();
                $table->string('pod_signature_path')->nullable();
                $table->decimal('pod_lat', 10, 7)->nullable();
                $table->decimal('pod_lng', 10, 7)->nullable();

                // Failure / Exception
                $table->string('failure_reason')->nullable();
                $table->text('failure_notes')->nullable();
                $table->string('failure_photo_path')->nullable();

                // Timestamps
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('arrived_pickup_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();

                $table->timestamps();
            });
        }

        // 5. Rider Job Offers (Broadcast Marketplace for available jobs)
        if (!Schema::hasTable('rider_job_offers')) {
            Schema::create('rider_job_offers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained('shipment_assignments')->onDelete('cascade');
                $table->foreignId('rider_profile_id')->constrained('rider_profiles')->onDelete('cascade');
                $table->decimal('offered_amount', 10, 2);
                $table->timestamp('offered_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->enum('response', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }

        // 6. Rider COD Ledgers (Strictly Segregated Cash-on-Delivery Ledger)
        if (!Schema::hasTable('rider_cod_ledgers')) {
            Schema::create('rider_cod_ledgers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rider_profile_id')->constrained('rider_profiles')->onDelete('cascade');
                $table->foreignId('assignment_id')->nullable()->constrained('shipment_assignments')->onDelete('set null');
                $table->enum('transaction_type', ['collected', 'deposited', 'settled', 'adjustment'])->default('collected');
                $table->decimal('amount', 12, 2); // Positive for collected cash, negative for deposit
                $table->decimal('balance_after', 12, 2)->default(0.00);
                $table->enum('deposit_method', ['cash_office', 'bank_transfer', 'digital_wallet', 'partner_point'])->nullable();
                $table->string('deposit_reference')->nullable();
                $table->string('deposit_receipt_path')->nullable();
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 7. Rider Earnings Ledgers (Strictly Segregated Rider Income)
        if (!Schema::hasTable('rider_earnings_ledgers')) {
            Schema::create('rider_earnings_ledgers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rider_profile_id')->constrained('rider_profiles')->onDelete('cascade');
                $table->foreignId('assignment_id')->nullable()->constrained('shipment_assignments')->onDelete('set null');
                $table->enum('type', ['delivery_fee', 'bonus', 'penalty', 'withdrawal', 'adjustment'])->default('delivery_fee');
                $table->decimal('amount', 10, 2);
                $table->decimal('balance_after', 10, 2)->default(0.00);
                $table->enum('status', ['pending', 'approved', 'withdrawn'])->default('approved');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 8. Rider Ratings & Reviews
        if (!Schema::hasTable('rider_ratings')) {
            Schema::create('rider_ratings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rider_profile_id')->constrained('rider_profiles')->onDelete('cascade');
                $table->foreignId('assignment_id')->constrained('shipment_assignments')->onDelete('cascade');
                $table->foreignId('rated_by_user_id')->constrained('users')->onDelete('cascade');
                $table->integer('overall_rating')->default(5);
                $table->integer('professionalism')->nullable();
                $table->integer('timeliness')->nullable();
                $table->integer('parcel_handling')->nullable();
                $table->integer('communication')->nullable();
                $table->text('feedback')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rider_ratings');
        Schema::dropIfExists('rider_earnings_ledgers');
        Schema::dropIfExists('rider_cod_ledgers');
        Schema::dropIfExists('rider_job_offers');
        Schema::dropIfExists('shipment_assignments');
        Schema::dropIfExists('rider_rate_rules');
        Schema::dropIfExists('rider_service_areas');
        Schema::dropIfExists('rider_profiles');
    }
};
