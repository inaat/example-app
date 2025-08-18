<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateInfo extends Model
{
    // Fillable fields for mass assignment
    protected $fillable = [
        'environment_type',
        'csr',
        'private_key',
        'OTP',
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
        // Organization Information
        'organization_identifier',
        'organization_name',
        'organization_unit_name',
        'common_name',
        'serial_number',
        'country_name',
        'invoice_type',
        'location_address',
        'business_category',
        'status',
        'otp_used',
        'vat_number',
        'address',
    ];

    /**
     * Get the next ICV (Invoice Counter Value) for this certificate
     */
    public function getNextICV()
    {
        $this->increment('lastICV');
        return $this->lastICV;
    }

    /**
     * Update the last invoice hash
     */
    public function updateLastInvoiceHash($hash)
    {
        $this->update(['lastInvoiceHash' => $hash]);
    }
}
