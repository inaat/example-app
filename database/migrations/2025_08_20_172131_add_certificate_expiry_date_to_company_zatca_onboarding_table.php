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
        Schema::table('company_zatca_onboarding', function (Blueprint $table) {
            $table->timestamp('certificate_expiry_date')->nullable()->after('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_zatca_onboarding', function (Blueprint $table) {
            $table->dropColumn('certificate_expiry_date');
        });
    }
};
