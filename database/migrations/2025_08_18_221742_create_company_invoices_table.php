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
        Schema::create('company_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_zatca_onboarding_id')->constrained('company_zatca_onboarding')->onDelete('cascade');
            $table->string('invoice_number');
            $table->string('uuid')->unique();
            $table->string('invoice_type', 3); // 388=Invoice, 381=Debit Note, 383=Credit Note
            $table->string('invoice_subtype', 2); // 01=Standard, 02=Simplified
            $table->date('issue_date');
            $table->time('issue_time');
            $table->date('due_date')->nullable();
            $table->integer('icv'); // Invoice Counter Value
            $table->string('previous_invoice_hash'); // PIH
            $table->string('current_hash')->nullable(); // Hash after signing
            
            // Seller and buyer information
            $table->json('seller_info');
            $table->json('buyer_info')->nullable();
            
            // Financial totals
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('SAR');
            
            // Line items
            $table->json('line_items');
            
            // XML and signing data
            $table->longText('invoice_xml')->nullable();
            $table->longText('signed_xml')->nullable();
            $table->text('qr_code')->nullable();
            
            // ZATCA submission data
            $table->string('zatca_status')->nullable(); // pending, reported, cleared, failed
            $table->json('zatca_response')->nullable();
            $table->string('zatca_uuid')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('cleared_at')->nullable();
            $table->text('error_message')->nullable();
            
            // Credit/Debit note specific fields
            $table->string('return_reason')->nullable();
            $table->string('original_invoice_number')->nullable();
            $table->foreignId('original_invoice_id')->nullable()->constrained('company_invoices');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['company_zatca_onboarding_id', 'invoice_type']);
            $table->index(['zatca_status']);
            $table->index(['issue_date']);
            $table->index(['invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_invoices');
    }
};