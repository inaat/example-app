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
        Schema::table('certificate_infos', function (Blueprint $table) {
            // Organization Information
            $table->string('organization_identifier')->comment('Organization identifier');
            $table->string('organization_name')->comment('Organization name');
            $table->string('organization_unit_name')->nullable()->comment('Organization unit name');
            $table->string('common_name')->comment('Common name');
            $table->string('serial_number')->comment('Certificate serial number');
            $table->string('country_name', 2)->default('SA')->comment('Country code');
            $table->string('invoice_type', 10)->default('1100')->comment('Invoice type');
            $table->string('location_address')->nullable()->comment('Location address');
            $table->string('business_category')->nullable()->comment('Business category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_infos', function (Blueprint $table) {
            $table->dropColumn([
                'organization_identifier',
                'organization_name',
                'organization_unit_name',
                'common_name',
                'serial_number',
                'country_name',
                'invoice_type',
                'location_address',
                'business_category'
            ]);
        });
    }
};
