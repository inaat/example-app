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
        Schema::table('zatca_invoices', function (Blueprint $table) {
            // Debit Note specific fields
            $table->string('debit_reason')->nullable()->comment('Reason for debit note/additional charges');
            $table->enum('debit_type', ['additional_charges', 'price_correction', 'extra_services'])->nullable()->comment('Type of debit note');
            
            // Add index for better query performance
            $table->index(['invoice_type', 'debit_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zatca_invoices', function (Blueprint $table) {
            $table->dropIndex(['invoice_type', 'debit_type']);
            
            $table->dropColumn([
                'debit_reason',
                'debit_type'
            ]);
        });
    }
};