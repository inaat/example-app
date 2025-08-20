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
        Schema::create('company_invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_invoice_id')->constrained('company_invoices')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price', 15, 2); // Price at time of invoice (may differ from current product price)
            $table->decimal('tax_rate', 5, 2); // Tax rate at time of invoice
            $table->decimal('line_total', 15, 2); // quantity * unit_price
            $table->decimal('tax_amount', 15, 2); // line_total * (tax_rate / 100)
            $table->decimal('total_with_tax', 15, 2); // line_total + tax_amount
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['company_invoice_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_invoice_line_items');
    }
};
