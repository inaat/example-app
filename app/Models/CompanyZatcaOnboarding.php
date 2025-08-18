<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyZatcaOnboarding extends Model
{
    use HasFactory;

    protected $table = 'company_zatca_onboarding';

    protected $fillable = [
        'portal_mode',
        'otp',
        'email',
        'common_name',
        'country_code',
        'organization_unit_name',
        'organization_name',
        'egs_serial_number',
        'serial_number',
        'vat_number',
        'vat_name',
        'invoice_type',
        'registered_address',
        'street_name',
        'building_number',
        'plot_identification',
        'sub_division_name',
        'city_name',
        'postal_number',
        'country_name',
        'business_category',
        'crn',
        'csr',
        'private_key',
        'ccsid_requestID',
        'ccsid_binarySecurityToken',
        'ccsid_secret',
        'pcsid_requestID',
        'pcsid_binarySecurityToken',
        'pcsid_secret',
        'lastICV',
        'lastInvoiceHash',
        'complianceCsidUrl',
        'complianceChecksUrl',
        'productionCsidUrl',
        'reportingUrl',
        'clearanceUrl',
        'status',
        'registration_date',
        'effective_date',
        'notes'
    ];

    protected $casts = [
        'registration_date' => 'datetime',
        'effective_date' => 'datetime',
    ];

    protected $attributes = [
        'portal_mode' => 'developer-portal',
        'country_code' => 'SA',
        'country_name' => 'Saudi Arabia',
        'invoice_type' => '1100',
        'status' => 'pending'
    ];
}
