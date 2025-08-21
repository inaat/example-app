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
        Schema::table('company_invoices', function (Blueprint $table) {
            $table->decimal('overall_discount_amount', 15, 2)->default(0)->after('discount_amount');
            $table->decimal('overall_discount_percentage', 5, 2)->default(0)->after('overall_discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_invoices', function (Blueprint $table) {
            $table->dropColumn(['overall_discount_amount', 'overall_discount_percentage']);
        });
    }
};
