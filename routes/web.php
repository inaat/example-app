<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestZatcaOnboardingController;


// Route::get('/', [App\Http\Controllers\TestController::class, 'index']);

// Route::get('/zatca/onboard', [TestZatcaOnboardingController::class, 'onboard']);


use App\Http\Controllers\ZatcaOnboardingController;
use App\Http\Controllers\ZatcaInvoiceController;
use App\Http\Controllers\ReturnInvoiceController;

Route::get('/', function () {
    return redirect()->route('zatca.onboarding.index');
});

// ZATCA Onboarding Routes
Route::prefix('zatca/onboarding')->name('zatca.onboarding.')->group(function () {
    Route::get('/', [ZatcaOnboardingController::class, 'index'])->name('index');
    Route::get('/create', [ZatcaOnboardingController::class, 'create'])->name('create');
    Route::post('/', [ZatcaOnboardingController::class, 'store'])->name('store');
    Route::get('/{certificate}', [ZatcaOnboardingController::class, 'show'])->name('show');
    Route::get('/{certificate}/edit', [ZatcaOnboardingController::class, 'edit'])->name('edit');
    Route::put('/{certificate}', [ZatcaOnboardingController::class, 'update'])->name('update');
    Route::post('/{certificate}/generate-csr', [ZatcaOnboardingController::class, 'generateCSR'])->name('generate-csr');
    Route::post('/{certificate}/compliance-csid', [ZatcaOnboardingController::class, 'getComplianceCSID'])->name('compliance-csid');
    Route::post('/{certificate}/production-csid', [ZatcaOnboardingController::class, 'getProductionCSID'])->name('production-csid');
    Route::delete('/{certificate}', [ZatcaOnboardingController::class, 'destroy'])->name('destroy');
});

// ZATCA Invoice Routes
Route::prefix('zatca/invoices')->name('zatca.invoices.')->group(function () {
    Route::get('/', [ZatcaInvoiceController::class, 'index'])->name('index');
    Route::get('/create', [ZatcaInvoiceController::class, 'create'])->name('create');
    Route::post('/', [ZatcaInvoiceController::class, 'store'])->name('store');
    Route::get('/{invoice}', [ZatcaInvoiceController::class, 'show'])->name('show');
    Route::get('/{invoice}/print', [ZatcaInvoiceController::class, 'print'])->name('print');
    Route::post('/{invoice}/generate-xml', [ZatcaInvoiceController::class, 'generateXML'])->name('generate-xml');
    Route::post('/{invoice}/sign', [ZatcaInvoiceController::class, 'signInvoice'])->name('sign');
    Route::post('/{invoice}/submit', [ZatcaInvoiceController::class, 'submitToZatca'])->name('submit');
    Route::post('/{invoice}/qr-code', [ZatcaInvoiceController::class, 'generateQRCode'])->name('qr-code');
    Route::delete('/{invoice}', [ZatcaInvoiceController::class, 'destroy'])->name('destroy');
});

// Return Invoices Routes
Route::prefix('zatca/returns')->name('zatca.returns.')->group(function () {
    Route::get('/', [ReturnInvoiceController::class, 'index'])->name('index');
    Route::get('/create', [ReturnInvoiceController::class, 'create'])->name('create');
    Route::post('/', [ReturnInvoiceController::class, 'store'])->name('store');
    Route::get('/{returnInvoice}', [ReturnInvoiceController::class, 'show'])->name('show');
    Route::get('/create-from/{invoice}', [ReturnInvoiceController::class, 'createFromInvoice'])->name('create-from');
    Route::post('/{returnInvoice}/generate-xml', [ReturnInvoiceController::class, 'generateReturnXML'])->name('generate-xml');
    Route::post('/{returnInvoice}/process', [ReturnInvoiceController::class, 'processReturn'])->name('process');
    Route::delete('/{returnInvoice}', [ReturnInvoiceController::class, 'destroy'])->name('destroy');
});
