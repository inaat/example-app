<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_infos', function (Blueprint $table) {
            $table->id();
            $table->string('environment_type')->unique();
            $table->text('csr')->nullable();
            $table->longText('private_key')->nullable();
            $table->string('OTP')->nullable();
            $table->string('ccsid_requestID')->nullable();
            $table->text('ccsid_binarySecurityToken')->nullable();
            $table->string('ccsid_secret')->nullable();
            $table->string('pcsid_requestID')->nullable();
            $table->text('pcsid_binarySecurityToken')->nullable();
            $table->string('pcsid_secret')->nullable();
            $table->bigInteger('lastICV')->default(0);
            $table->text('lastInvoiceHash')->nullable();
            $table->string('complianceCsidUrl')->nullable();
            $table->string('complianceChecksUrl')->nullable();
            $table->string('productionCsidUrl')->nullable();
            $table->string('reportingUrl')->nullable();
            $table->string('clearanceUrl')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_infos');
    }
};
