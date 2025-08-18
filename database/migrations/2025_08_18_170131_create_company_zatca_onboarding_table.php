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
        Schema::create('company_zatca_onboarding', function (Blueprint $table) {
            $table->id();
            
            // Portal Configuration
            $table->enum('portal_mode', ['developer-portal', 'simulation', 'core'])->default('developer-portal');
            $table->string('otp', 20);
            $table->string('email')->index();
            
            // Certificate Information
            $table->string('common_name');
            $table->string('country_code', 2)->default('SA');
            $table->string('organization_unit_name')->nullable();
            $table->string('organization_name');
            $table->string('egs_serial_number');
            $table->string('serial_number')->nullable(); // Generated certificate serial number
            
            // VAT Information
            $table->string('vat_number', 15)->unique();
            $table->string('vat_name');
            $table->enum('invoice_type', ['0100', '1000', '1100'])->default('1100');
            
            // Address Information
            $table->string('registered_address');
            $table->string('street_name');
            $table->string('building_number');
            $table->string('plot_identification')->nullable();
            $table->string('sub_division_name')->nullable();
            $table->string('city_name');
            $table->string('postal_number');
            $table->string('country_name')->default('Saudi Arabia');
            
            // Business Information
            $table->string('business_category');
            $table->string('crn')->nullable(); // Commercial Registration Number
            // Certificate and API URLs
            $table->text('csr')->nullable();
            $table->longText('private_key')->nullable();
            $table->string('ccsid_requestID')->nullable();
            $table->text('ccsid_binarySecurityToken')->nullable();
            $table->string('ccsid_secret')->nullable();
            $table->string('pcsid_requestID')->nullable();
            $table->text('pcsid_binarySecurityToken')->nullable();
            $table->string('pcsid_secret')->nullable();
            $table->bigInteger('lastICV')->default(0);
            $table->text('lastInvoiceHash')->nullable();
            
            // API URLs
            $table->string('complianceCsidUrl')->nullable();
            $table->string('complianceChecksUrl')->nullable();
            $table->string('productionCsidUrl')->nullable();
            $table->string('reportingUrl')->nullable();
            $table->string('clearanceUrl')->nullable();
            // Status and Timestamps
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->timestamp('registration_date')->nullable();
            $table->timestamp('effective_date')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'portal_mode']);
            $table->index('registration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_zatca_onboarding');
    }
};
