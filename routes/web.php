<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestZatcaOnboardingController;


Route::get('/new', [App\Http\Controllers\TestController::class, 'test']);

// Route::get('/zatca/onboard', [TestZatcaOnboardingController::class, 'onboard']);


use App\Http\Controllers\ZatcaOnboardingController;
use App\Http\Controllers\ZatcaInvoiceController;
use App\Http\Controllers\ReturnInvoiceController;
use App\Http\Controllers\CompanyOnboardingController;
use App\Http\Controllers\CompanyInvoiceController;

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

// Company ZATCA Onboarding Routes
Route::prefix('company-onboarding')->name('company-onboarding.')->group(function () {
    Route::get('/', [CompanyOnboardingController::class, 'create'])->name('create');
    Route::post('/', [CompanyOnboardingController::class, 'store'])->name('store');
});

// Company Invoice Routes
Route::prefix('zatca/company/invoices')->name('zatca.company.invoices.')->group(function () {
    Route::get('/', [CompanyInvoiceController::class, 'index'])->name('index');
    Route::get('/create', [CompanyInvoiceController::class, 'create'])->name('create');
    Route::post('/', [CompanyInvoiceController::class, 'store'])->name('store');
    Route::get('/{invoice}', [CompanyInvoiceController::class, 'show'])->name('show');
    Route::get('/{invoice}/print', [CompanyInvoiceController::class, 'print'])->name('print');
    Route::post('/{invoice}/generate-xml', [CompanyInvoiceController::class, 'generateXML'])->name('generate-xml');
    Route::post('/{invoice}/sign', [CompanyInvoiceController::class, 'signInvoice'])->name('sign');
    Route::post('/{invoice}/submit', [CompanyInvoiceController::class, 'submitToZatca'])->name('submit');
    Route::post('/{invoice}/qr-code', [CompanyInvoiceController::class, 'generateQRCode'])->name('qr-code');
    Route::get('/{invoice}/create-return', [CompanyInvoiceController::class, 'createReturn'])->name('create-return');
    Route::get('/{invoice}/create-debit', [CompanyInvoiceController::class, 'createDebit'])->name('create-debit');
    Route::delete('/{invoice}', [CompanyInvoiceController::class, 'destroy'])->name('destroy');
});

// Company Returns Routes
Route::prefix('zatca/company/returns')->name('zatca.company.returns.')->group(function () {
    Route::get('/', [CompanyInvoiceController::class, 'returnsIndex'])->name('index');
    Route::get('/{invoice}', [CompanyInvoiceController::class, 'show'])->name('show');
    Route::get('/{invoice}/print', [CompanyInvoiceController::class, 'print'])->name('print');
    Route::post('/{invoice}/generate-xml', [CompanyInvoiceController::class, 'generateXML'])->name('generate-xml');
    Route::post('/{invoice}/sign', [CompanyInvoiceController::class, 'signInvoice'])->name('sign');
    Route::post('/{invoice}/submit', [CompanyInvoiceController::class, 'submitToZatca'])->name('submit');
    Route::post('/{invoice}/qr-code', [CompanyInvoiceController::class, 'generateQRCode'])->name('qr-code');
    Route::delete('/{invoice}', [CompanyInvoiceController::class, 'destroy'])->name('destroy');
});

// Company Debits Routes
Route::prefix('zatca/company/debits')->name('zatca.company.debits.')->group(function () {
    Route::get('/', [CompanyInvoiceController::class, 'debitsIndex'])->name('index');
    Route::get('/{invoice}', [CompanyInvoiceController::class, 'show'])->name('show');
    Route::get('/{invoice}/print', [CompanyInvoiceController::class, 'print'])->name('print');
    Route::post('/{invoice}/generate-xml', [CompanyInvoiceController::class, 'generateXML'])->name('generate-xml');
    Route::post('/{invoice}/sign', [CompanyInvoiceController::class, 'signInvoice'])->name('sign');
    Route::post('/{invoice}/submit', [CompanyInvoiceController::class, 'submitToZatca'])->name('submit');
    Route::post('/{invoice}/qr-code', [CompanyInvoiceController::class, 'generateQRCode'])->name('qr-code');
    Route::delete('/{invoice}', [CompanyInvoiceController::class, 'destroy'])->name('destroy');
});

// Debit Notes Routes
Route::prefix('zatca/debits')->name('zatca.debits.')->group(function () {
    Route::get('/', [App\Http\Controllers\DebitNoteController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\DebitNoteController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\DebitNoteController::class, 'store'])->name('store');
    Route::get('/{debitNote}', [App\Http\Controllers\DebitNoteController::class, 'show'])->name('show');
    Route::get('/{debitNote}/print', [App\Http\Controllers\DebitNoteController::class, 'print'])->name('print');
    Route::get('/create-from/{invoice}', [App\Http\Controllers\DebitNoteController::class, 'createFromInvoice'])->name('create-from');
    Route::post('/{debitNote}/generate-xml', [App\Http\Controllers\DebitNoteController::class, 'generateDebitXML'])->name('generate-xml');
    Route::post('/{debitNote}/process', [App\Http\Controllers\DebitNoteController::class, 'processDebit'])->name('process');
    Route::delete('/{debitNote}', [App\Http\Controllers\DebitNoteController::class, 'destroy'])->name('destroy');
});
