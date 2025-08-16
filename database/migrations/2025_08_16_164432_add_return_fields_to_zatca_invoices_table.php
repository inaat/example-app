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
            // Return/Credit Note specific fields
            $table->string('return_reason')->nullable()->comment('Reason for return/credit note');
            $table->string('original_invoice_number')->nullable()->comment('Original invoice number being returned');
            $table->unsignedBigInteger('original_invoice_id')->nullable()->comment('Reference to original invoice ID');
            $table->enum('return_type', ['full', 'partial'])->nullable()->comment('Type of return: full or partial');
            $table->timestamp('return_date')->nullable()->comment('Date when return was processed');
            
            // Add foreign key constraint
            $table->foreign('original_invoice_id')->references('id')->on('zatca_invoices')->onDelete('set null');
            
            // Add index for better query performance
            $table->index(['invoice_type', 'return_type']);
            $table->index('original_invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zatca_invoices', function (Blueprint $table) {
            $table->dropForeign(['original_invoice_id']);
            $table->dropIndex(['invoice_type', 'return_type']);
            $table->dropIndex(['original_invoice_number']);
            
            $table->dropColumn([
                'return_reason',
                'original_invoice_number', 
                'original_invoice_id',
                'return_type',
                'return_date'
            ]);
        });
    }
};