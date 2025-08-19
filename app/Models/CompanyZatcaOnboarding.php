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

    /**
     * Get company invoices
     */
    public function invoices()
    {
        return $this->hasMany(CompanyInvoice::class, 'company_zatca_onboarding_id');
    }

    /**
     * Get the next ICV (Invoice Counter Value) for this company
     */
    public function getNextICV()
    {
        $currentICV = $this->lastICV ?? 0;
        $nextICV = $currentICV + 1;
        
        // Update the lastICV in database
        $this->update(['lastICV' => $nextICV]);
        
        return $nextICV;
    }

    /**
     * Update the last invoice hash for this company
     */
    public function updateLastInvoiceHash($hash)
    {
        $this->update(['lastInvoiceHash' => $hash]);
        return $this;
    }

    /**
     * Check if company is successfully onboarded
     */
    public function isOnboarded()
    {
        return $this->status === 'success';
    }

    /**
     * Check if company onboarding is in progress
     */
    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    /**
     * Check if company onboarding failed
     */
    public function hasFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Get the production certificate if available, otherwise compliance certificate
     */
    public function getActiveCertificate()
    {
        return $this->pcsid_binarySecurityToken ?: $this->ccsid_binarySecurityToken;
    }

    /**
     * Get the production secret if available, otherwise compliance secret
     */
    public function getActiveSecret()
    {
        return $this->pcsid_secret ?: $this->ccsid_secret;
    }

    /**
     * Check if company has production certificate
     */
    public function hasProductionCertificate()
    {
        return !empty($this->pcsid_binarySecurityToken);
    }

    /**
     * Check if company has compliance certificate
     */
    public function hasComplianceCertificate()
    {
        return !empty($this->ccsid_binarySecurityToken);
    }

    /**
     * Get the portal environment name for display
     */
    public function getPortalEnvironment()
    {
        return match($this->portal_mode) {
            'core' => 'Production',
            'simulation' => 'Simulation',
            'developer-portal' => 'Developer Portal',
            default => 'Unknown'
        };
    }

    /**
     * Get the status badge color for display
     */
    public function getStatusBadgeColor()
    {
        return match($this->status) {
            'success' => 'success',
            'processing' => 'warning',
            'failed' => 'danger',
            'pending' => 'secondary',
            default => 'secondary'
        };
    }
}
