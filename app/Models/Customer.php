<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vat_number',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'country',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get invoices for this customer
     */
    public function invoices()
    {
        return $this->hasMany(CompanyInvoice::class);
    }

    /**
     * Scope for active customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted full address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Get display name with VAT if available
     */
    public function getDisplayNameAttribute()
    {
        if ($this->vat_number) {
            return $this->name . ' (VAT: ' . $this->vat_number . ')';
        }
        return $this->name;
    }
}
