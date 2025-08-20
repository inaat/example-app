<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'unit_price',
        'tax_rate',
        'unit_of_measure',
        'is_active'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get active products only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted price with currency
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->unit_price, 2) . ' SAR';
    }

    /**
     * Get invoice line items for this product
     */
    public function invoiceLineItems()
    {
        return $this->hasMany(CompanyInvoiceLineItem::class);
    }
}
