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
        Schema::create('zatca_invoices', function (Blueprint $table) {
            $table->id();
            
            // Certificate Reference
            $table->foreignId('certificate_info_id')->constrained('certificate_infos')->onDelete('cascade');
            
            // Invoice Basic Information
            $table->string('invoice_number')->comment('Invoice number');
            $table->string('uuid')->unique()->comment('Invoice UUID');
            $table->enum('invoice_type', ['388', '381', '383'])->comment('Invoice type: 388=Standard, 381=Credit, 383=Debit');
            $table->enum('invoice_subtype', ['01', '02'])->comment('Invoice subtype: 01=Standard, 02=Simplified');
            $table->date('issue_date')->comment('Invoice issue date');
            $table->time('issue_time')->comment('Invoice issue time');
            $table->date('due_date')->nullable()->comment('Invoice due date');
            
            // Counter and Hash
            $table->integer('icv')->comment('Invoice Counter Value');
            $table->text('previous_invoice_hash')->nullable()->comment('Previous invoice hash');
            $table->text('current_hash')->nullable()->comment('Current invoice hash');
            
            // Seller Information
            $table->json('seller_info')->comment('Seller information JSON');
            
            // Buyer Information
            $table->json('buyer_info')->nullable()->comment('Buyer information JSON');
            
            // Financial Information
            $table->decimal('subtotal', 10, 2)->default(0)->comment('Invoice subtotal');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('Total tax amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Total discount amount');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('Invoice total amount');
            $table->string('currency', 3)->default('SAR')->comment('Currency code');
            
            // Invoice Items
            $table->json('line_items')->comment('Invoice line items JSON');
            
            // Tax Information
            $table->json('tax_breakdown')->nullable()->comment('Tax breakdown JSON');
            
            // QR Code and XML
            $table->text('qr_code')->nullable()->comment('Generated QR code');
            $table->longText('invoice_xml')->nullable()->comment('Generated invoice XML');
            $table->longText('signed_xml')->nullable()->comment('Signed invoice XML');
            
            // ZATCA Response
            $table->enum('zatca_status', ['pending', 'reported', 'cleared', 'failed'])->default('pending')
                  ->comment('ZATCA submission status');
            $table->json('zatca_response')->nullable()->comment('ZATCA API response JSON');
            $table->string('zatca_uuid')->nullable()->comment('ZATCA assigned UUID');
            $table->timestamp('submitted_at')->nullable()->comment('Submitted to ZATCA at');
            $table->timestamp('cleared_at')->nullable()->comment('Cleared by ZATCA at');
            
            // Error Handling
            $table->text('error_message')->nullable()->comment('Error message if failed');
            $table->json('validation_errors')->nullable()->comment('Validation errors JSON');
            
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_number', 'idx_invoice_number');
            $table->index('zatca_status', 'idx_zatca_status');
            $table->index('issue_date', 'idx_issue_date');
            $table->index('icv', 'idx_icv');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zatca_invoices');
    }
};
