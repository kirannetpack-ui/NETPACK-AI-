<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add verification columns to users table (check if they exist first)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'citizenship_number')) {
                $table->string('citizenship_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'citizenship_front')) {
                $table->string('citizenship_front')->nullable();
            }
            if (!Schema::hasColumn('users', 'citizenship_back')) {
                $table->string('citizenship_back')->nullable();
            }
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable();
            }
            if (!Schema::hasColumn('users', 'business_name')) {
                $table->string('business_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'business_address')) {
                $table->string('business_address')->nullable();
            }
            if (!Schema::hasColumn('users', 'pan_number')) {
                $table->string('pan_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'driving_license_front')) {
                $table->string('driving_license_front')->nullable();
            }
            if (!Schema::hasColumn('users', 'driving_license_back')) {
                $table->string('driving_license_back')->nullable();
            }
            if (!Schema::hasColumn('users', 'verification_status')) {
                $table->enum('verification_status', ['pending', 'approved', 'rejected', 'document_pending'])->default('pending');
            }
            if (!Schema::hasColumn('users', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable();
            }
        });
        
        // Create verification_documents table
        if (!Schema::hasTable('verification_documents')) {
            Schema::create('verification_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('document_type');
                $table->string('document_number')->nullable();
                $table->string('file_path');
                $table->string('file_name');
                $table->text('notes')->nullable();
                $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Drop verification_documents table first
        Schema::dropIfExists('verification_documents');
        
        // Drop columns from users (no foreign key issues here)
        Schema::table('users', function (Blueprint $table) {
            $columns = ['citizenship_number', 'citizenship_front', 'citizenship_back', 
                'profile_photo', 'business_name', 'business_address', 'pan_number',
                'driving_license_front', 'driving_license_back', 'verification_status',
                'submitted_at', 'reviewed_at', 'reviewed_by'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};