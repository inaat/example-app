<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_zatca_onboarding_id',
        'customer_id',
        'invoice_number',
        'uuid',
        'invoice_type',
        'invoice_subtype',
        'issue_date',
        'issue_time',
        'due_date',
        'icv',
        'previous_invoice_hash',
        'current_hash',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'overall_discount_amount',
        'overall_discount_percentage',
        'total_amount',
        'currency',
        'invoice_xml',
        'signed_xml',
        'qr_code',
        'zatca_status',
        'zatca_response',
        'zatca_uuid',
        'submitted_at',
        'cleared_at',
        'error_message',
        'return_reason',
        'original_invoice_number',
        'original_invoice_id'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'cleared_at' => 'datetime',
        'zatca_response' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $dates = [
        'issue_date',
        'due_date',
        'submitted_at',
        'cleared_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the company that owns this invoice
     */
    public function company()
    {
        return $this->belongsTo(CompanyZatcaOnboarding::class, 'company_zatca_onboarding_id');
    }

    /**
     * Get the customer for this invoice
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the line items for this invoice
     */
    public function lineItems()
    {
        return $this->hasMany(CompanyInvoiceLineItem::class);
    }

    /**
     * Get return invoices (credit notes) for this invoice
     */
    public function returns()
    {
        return $this->hasMany(CompanyInvoice::class, 'original_invoice_id')
                    ->where('invoice_type', '383'); // Credit notes
    }

    /**
     * Get debit notes for this invoice
     */
    public function debits()
    {
        return $this->hasMany(CompanyInvoice::class, 'original_invoice_id')
                    ->where('invoice_type', '381'); // Debit notes
    }

    /**
     * Get the original invoice if this is a credit/debit note
     */
    public function originalInvoice()
    {
        return $this->belongsTo(CompanyInvoice::class, 'original_invoice_id');
    }

    /**
     * Check if this is a simplified invoice
     */
    public function isSimplified()
    {
        return $this->invoice_subtype === '02';
    }

    /**
     * Check if this is a standard invoice
     */
    public function isStandard()
    {
        return $this->invoice_subtype === '01';
    }

    /**
     * Check if this is a credit note
     */
    public function isCreditNote()
    {
        return $this->invoice_type === '383';
    }

    /**
     * Check if this is a debit note
     */
    public function isDebitNote()
    {
        return $this->invoice_type === '381';
    }

    /**
     * Check if this is a regular invoice
     */
    public function isInvoice()
    {
        return $this->invoice_type === '388';
    }

    /**
     * Check if invoice has been submitted to ZATCA
     */
    public function isSubmitted()
    {
        return in_array($this->zatca_status, ['reported', 'cleared']);
    }

    /**
     * Check if invoice has been cleared by ZATCA
     */
    public function isCleared()
    {
        return $this->zatca_status === 'cleared';
    }

    /**
     * Check if invoice has been reported to ZATCA
     */
    public function isReported()
    {
        return $this->zatca_status === 'reported';
    }

    /**
     * Get the status badge color for display
     */
    public function getStatusBadgeColor()
    {
        return match($this->zatca_status) {
            'cleared' => 'success',
            'reported' => 'info',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get the formatted total amount with currency
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get the formatted issue date and time
     */
    public function getFormattedIssueDateTimeAttribute()
    {
        return $this->issue_date->format('Y-m-d') . ' ' . $this->issue_time;
    }

    /**
     * Scope for filtering by invoice type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('invoice_type', $type);
    }

    /**
     * Scope for filtering by invoice subtype
     */
    public function scopeOfSubtype($query, $subtype)
    {
        return $query->where('invoice_subtype', $subtype);
    }

    /**
     * Scope for submitted invoices
     */
    public function scopeSubmitted($query)
    {
        return $query->whereIn('zatca_status', ['reported', 'cleared']);
    }

    /**
     * Scope for pending invoices
     */
    public function scopePending($query)
    {
        return $query->whereNull('zatca_status')->orWhere('zatca_status', 'pending');
    }

    /**
     * Scope for failed invoices
     */
    public function scopeFailed($query)
    {
        return $query->where('zatca_status', 'failed');
    }

    /**
     * Check if invoice is pending (not submitted)
     */
    public function isPending()
    {
        return !$this->isSubmitted();
    }

    /**
     * Check if this is a standard invoice (subtype 01)
     */
    public function isStandardInvoice()
    {
        return $this->invoice_subtype === '01';
    }

    /**
     * Get the invoice type name for display
     */
    public function getInvoiceTypeName()
    {
        return match($this->invoice_type) {
            '388' => 'Invoice',
            '381' => 'Debit Note',
            '383' => 'Credit Note',
            default => 'Unknown'
        };
    }

    /**
     * Check if this invoice has returns (credit notes)
     */
    public function hasReturns()
    {
        return $this->returns()->exists();
    }

    /**
     * Check if this invoice has debit notes
     */
    public function hasDebits()
    {
        return $this->debits()->exists();
    }
}