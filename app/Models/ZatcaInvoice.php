<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZatcaInvoice extends Model
{
    protected $fillable = [
        'certificate_info_id',
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
        'seller_info',
        'buyer_info',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'line_items',
        'tax_breakdown',
        'qr_code',
        'invoice_xml',
        'signed_xml',
        'zatca_status',
        'zatca_response',
        'zatca_uuid',
        'submitted_at',
        'cleared_at',
        'error_message',
        'validation_errors',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'seller_info' => 'array',
        'buyer_info' => 'array',
        'line_items' => 'array',
        'tax_breakdown' => 'array',
        'zatca_response' => 'array',
        'validation_errors' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'cleared_at' => 'datetime',
        'icv' => 'integer',
    ];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(CertificateInfo::class, 'certificate_info_id');
    }

    public function isPending(): bool
    {
        return $this->zatca_status === 'pending';
    }

    public function isReported(): bool
    {
        return $this->zatca_status === 'reported';
    }

    public function isCleared(): bool
    {
        return $this->zatca_status === 'cleared';
    }

    public function isFailed(): bool
    {
        return $this->zatca_status === 'failed';
    }

    public function isStandardInvoice(): bool
    {
        return $this->invoice_subtype === '01';
    }

    public function isSimplifiedInvoice(): bool
    {
        return $this->invoice_subtype === '02';
    }

    public function getInvoiceTypeName(): string
    {
        return match($this->invoice_type) {
            '388' => 'Standard Invoice',
            '381' => 'Credit Note',
            '383' => 'Debit Note',
            default => 'Unknown'
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->zatca_status) {
            'pending' => 'warning',
            'reported' => 'info',
            'cleared' => 'success',
            'failed' => 'danger',
            default => 'secondary'
        };
    }
}
