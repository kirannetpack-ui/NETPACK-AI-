<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if columns exist before adding
            
            // Basic Information
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->after('name');
            }
            
            // Address fields
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'district')) {
                $table->string('district')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'province')) {
                $table->string('province')->nullable()->after('district');
            }
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->default('Nepal')->after('province');
            }
            if (!Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('country');
            }
            
            // Document fields (minimal for registration)
            if (!Schema::hasColumn('users', 'citizenship_number')) {
                $table->string('citizenship_number')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('users', 'citizenship_front')) {
                $table->string('citizenship_front')->nullable()->after('citizenship_number');
            }
            if (!Schema::hasColumn('users', 'citizenship_back')) {
                $table->string('citizenship_back')->nullable()->after('citizenship_front');
            }
            
            // Business fields for sellers and partners
            if (!Schema::hasColumn('users', 'business_name')) {
                $table->string('business_name')->nullable()->after('citizenship_back');
            }
            if (!Schema::hasColumn('users', 'business_address')) {
                $table->text('business_address')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('users', 'pan_number')) {
                $table->string('pan_number')->nullable()->after('business_address');
            }
            if (!Schema::hasColumn('users', 'business_registration_number')) {
                $table->string('business_registration_number')->nullable()->after('pan_number');
            }
            if (!Schema::hasColumn('users', 'business_registration_document')) {
                $table->string('business_registration_document')->nullable()->after('business_registration_number');
            }
            
            // Rider specific fields
            if (!Schema::hasColumn('users', 'license_number')) {
                $table->string('license_number')->nullable()->after('business_registration_document');
            }
            if (!Schema::hasColumn('users', 'license_front')) {
                $table->string('license_front')->nullable()->after('license_number');
            }
            if (!Schema::hasColumn('users', 'license_back')) {
                $table->string('license_back')->nullable()->after('license_front');
            }
            if (!Schema::hasColumn('users', 'vehicle_type')) {
                $table->string('vehicle_type')->nullable()->after('license_back');
            }
            if (!Schema::hasColumn('users', 'vehicle_registration_number')) {
                $table->string('vehicle_registration_number')->nullable()->after('vehicle_type');
            }
            if (!Schema::hasColumn('users', 'vehicle_registration_document')) {
                $table->string('vehicle_registration_document')->nullable()->after('vehicle_registration_number');
            }
            
            // Profile photo
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('vehicle_registration_document');
            }
            
            // Approval and status fields
            if (!Schema::hasColumn('users', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('verification_status');
            }
            if (!Schema::hasColumn('users', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('users', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('users', 'registration_completed')) {
                $table->boolean('registration_completed')->default(false)->after('rejection_reason');
            }
            
            // KYC fields (for later update)
            if (!Schema::hasColumn('users', 'kyc_verified')) {
                $table->boolean('kyc_verified')->default(false)->after('registration_completed');
            }
            if (!Schema::hasColumn('users', 'kyc_verified_at')) {
                $table->timestamp('kyc_verified_at')->nullable()->after('kyc_verified');
            }
            if (!Schema::hasColumn('users', 'kyc_documents')) {
                $table->json('kyc_documents')->nullable()->after('kyc_verified_at');
            }
            
            // Additional fields
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('kyc_documents');
            }
            if (!Schema::hasColumn('users', 'dob')) {
                $table->date('dob')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'nationality')) {
                $table->string('nationality')->default('Nepali')->after('dob');
            }
            if (!Schema::hasColumn('users', 'emergency_contact')) {
                $table->string('emergency_contact')->nullable()->after('nationality');
            }
            
            // Last login
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('emergency_contact');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'username', 'address', 'city', 'district', 'province', 'country',
                'postal_code', 'citizenship_number', 'citizenship_front', 'citizenship_back',
                'business_name', 'business_address', 'pan_number', 'business_registration_number',
                'business_registration_document', 'license_number', 'license_front', 'license_back',
                'vehicle_type', 'vehicle_registration_number', 'vehicle_registration_document',
                'profile_photo', 'approved_at', 'approved_by', 'rejection_reason',
                'registration_completed', 'kyc_verified', 'kyc_verified_at', 'kyc_documents',
                'gender', 'dob', 'nationality', 'emergency_contact', 'last_login_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};