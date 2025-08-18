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
            $table->string('otp_used', 20)->nullable()->after('environment_type');
            $table->string('vat_number', 255)->nullable()->after('otp_used');
            $table->text('address')->nullable()->after('vat_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_infos', function (Blueprint $table) {
            $table->dropColumn(['otp_used', 'vat_number', 'address']);
        });
    }
};
