<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInvoiceLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'tax_rate',
        'line_total',
        'tax_amount',
        'total_with_tax'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_with_tax' => 'decimal:2'
    ];

    /**
     * Get the invoice this line item belongs to
     */
    public function invoice()
    {
        return $this->belongsTo(CompanyInvoice::class, 'company_invoice_id');
    }

    /**
     * Get the product for this line item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate totals automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($lineItem) {
            $lineItem->line_total = $lineItem->quantity * $lineItem->unit_price;
            $lineItem->tax_amount = $lineItem->line_total * ($lineItem->tax_rate / 100);
            $lineItem->total_with_tax = $lineItem->line_total + $lineItem->tax_amount;
        });
    }
}
