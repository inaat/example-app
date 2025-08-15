@extends('layouts.app')

@section('title', 'Create Invoice')
@section('page-title', 'Create ZATCA Invoice')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Invoice Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('zatca.invoices.store') }}" method="POST" id="invoiceForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="certificate_info_id" class="form-label">ZATCA Certificate *</label>
                            <select class="form-select @error('certificate_info_id') is-invalid @enderror" 
                                    id="certificate_info_id" name="certificate_info_id" required>
                                <option value="">Select Certificate</option>
                                @foreach($certificates as $cert)
                                    <option value="{{ $cert->id }}" {{ old('certificate_info_id') == $cert->id ? 'selected' : '' }}>
                                        {{ $cert->organization_name }} ({{ $cert->environment_type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('certificate_info_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="invoice_number" class="form-label">Invoice Number *</label>
                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                   id="invoice_number" name="invoice_number" 
                                   value="{{ old('invoice_number', 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)) }}" required>
                            @error('invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="invoice_type" class="form-label">Invoice Type *</label>
                            <select class="form-select @error('invoice_type') is-invalid @enderror" 
                                    id="invoice_type" name="invoice_type" required>
                                <option value="388" {{ old('invoice_type', '388') == '388' ? 'selected' : '' }}>Standard Invoice (388)</option>
                                <option value="381" {{ old('invoice_type') == '381' ? 'selected' : '' }}>Credit Note (381)</option>
                                <option value="383" {{ old('invoice_type') == '383' ? 'selected' : '' }}>Debit Note (383)</option>
                            </select>
                            @error('invoice_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="invoice_subtype" class="form-label">Invoice Subtype *</label>
                            <select class="form-select @error('invoice_subtype') is-invalid @enderror" 
                                    id="invoice_subtype" name="invoice_subtype" required>
                                <option value="01" {{ old('invoice_subtype', '01') == '01' ? 'selected' : '' }}>Standard (01)</option>
                                <option value="02" {{ old('invoice_subtype') == '02' ? 'selected' : '' }}>Simplified (02)</option>
                            </select>
                            @error('invoice_subtype')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="issue_date" class="form-label">Issue Date *</label>
                            <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                   id="issue_date" name="issue_date" 
                                   value="{{ old('issue_date', date('Y-m-d')) }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="issue_time" class="form-label">Issue Time *</label>
                            <input type="time" class="form-control @error('issue_time') is-invalid @enderror" 
                                   id="issue_time" name="issue_time" 
                                   value="{{ old('issue_time', date('H:i')) }}" required>
                            @error('issue_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                               id="due_date" name="due_date" 
                               value="{{ old('due_date') }}">
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Seller Information -->
                    <h6 class="border-bottom pb-2 mb-3">Seller Information</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="seller_name" class="form-label">Seller Name *</label>
                            <input type="text" class="form-control @error('seller_name') is-invalid @enderror" 
                                   id="seller_name" name="seller_name" 
                                   value="{{ old('seller_name', 'Maximum Speed Tech Supply LTD') }}" required>
                            @error('seller_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="seller_vat" class="form-label">Seller VAT Number *</label>
                            <input type="text" class="form-control @error('seller_vat') is-invalid @enderror" 
                                   id="seller_vat" name="seller_vat" 
                                   value="{{ old('seller_vat', '399999999900003') }}" required>
                            @error('seller_vat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="seller_address" class="form-label">Seller Address *</label>
                        <textarea class="form-control @error('seller_address') is-invalid @enderror" 
                                  id="seller_address" name="seller_address" rows="2" required>{{ old('seller_address', 'Riyadh, Saudi Arabia') }}</textarea>
                        @error('seller_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Buyer Information -->
                    <h6 class="border-bottom pb-2 mb-3">Buyer Information</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="buyer_name" class="form-label">Buyer Name</label>
                            <input type="text" class="form-control @error('buyer_name') is-invalid @enderror" 
                                   id="buyer_name" name="buyer_name" 
                                   value="{{ old('buyer_name') }}">
                            @error('buyer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="buyer_vat" class="form-label">Buyer VAT Number</label>
                            <input type="text" class="form-control @error('buyer_vat') is-invalid @enderror" 
                                   id="buyer_vat" name="buyer_vat" 
                                   value="{{ old('buyer_vat') }}">
                            @error('buyer_vat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="buyer_address" class="form-label">Buyer Address</label>
                        <textarea class="form-control @error('buyer_address') is-invalid @enderror" 
                                  id="buyer_address" name="buyer_address" rows="2">{{ old('buyer_address') }}</textarea>
                        @error('buyer_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="currency" class="form-label">Currency *</label>
                        <select class="form-select @error('currency') is-invalid @enderror" 
                                id="currency" name="currency" required>
                            <option value="SAR" {{ old('currency', 'SAR') == 'SAR' ? 'selected' : '' }}>SAR - Saudi Riyal</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Line Items -->
                    <h6 class="border-bottom pb-2 mb-3">Invoice Items</h6>
                    
                    <div id="lineItems">
                        <div class="line-item border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Item Name *</label>
                                    <input type="text" class="form-control" name="line_items[0][name]" 
                                           value="{{ old('line_items.0.name', 'Sample Product') }}" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Quantity *</label>
                                    <input type="number" class="form-control quantity" name="line_items[0][quantity]" 
                                           value="{{ old('line_items.0.quantity', '1') }}" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Unit Price *</label>
                                    <input type="number" class="form-control unit-price" name="line_items[0][unit_price]" 
                                           value="{{ old('line_items.0.unit_price', '100') }}" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Tax Rate (%) *</label>
                                    <input type="number" class="form-control tax-rate" name="line_items[0][tax_rate]" 
                                           value="{{ old('line_items.0.tax_rate', '15') }}" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Total</label>
                                    <input type="text" class="form-control line-total" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" onclick="addLineItem()">
                            <i class="fas fa-plus me-2"></i>Add Item
                        </button>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('zatca.invoices.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calculator me-2"></i>Invoice Summary
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end" id="subtotal">0.00</td>
                    </tr>
                    <tr>
                        <td>Tax Amount:</td>
                        <td class="text-end" id="taxAmount">0.00</td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Total:</strong></td>
                        <td class="text-end"><strong id="totalAmount">0.00</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Information
                </h6>
            </div>
            <div class="card-body">
                <p class="small">Create a new invoice for ZATCA submission.</p>
                
                <h6>Next Steps:</h6>
                <ol class="small">
                    <li>Create invoice</li>
                    <li>Generate XML</li>
                    <li>Sign invoice</li>
                    <li>Submit to ZATCA</li>
                    <li>Generate QR code</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let itemIndex = 1;

function addLineItem() {
    const container = document.getElementById('lineItems');
    const newItem = document.createElement('div');
    newItem.className = 'line-item border rounded p-3 mb-3';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label">Item Name *</label>
                <input type="text" class="form-control" name="line_items[${itemIndex}][name]" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Quantity *</label>
                <input type="number" class="form-control quantity" name="line_items[${itemIndex}][quantity]" 
                       value="1" min="0" step="0.01" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Unit Price *</label>
                <input type="number" class="form-control unit-price" name="line_items[${itemIndex}][unit_price]" 
                       value="0" min="0" step="0.01" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Tax Rate (%) *</label>
                <input type="number" class="form-control tax-rate" name="line_items[${itemIndex}][tax_rate]" 
                       value="15" min="0" step="0.01" required>
            </div>
            <div class="col-md-1 mb-2">
                <label class="form-label">Total</label>
                <input type="text" class="form-control line-total" readonly>
            </div>
            <div class="col-md-1 mb-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger btn-sm d-block" onclick="removeLineItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    itemIndex++;
    
    // Attach event listeners to new inputs
    attachLineItemListeners(newItem);
    calculateTotals();
}

function removeLineItem(button) {
    button.closest('.line-item').remove();
    calculateTotals();
}

function attachLineItemListeners(item) {
    const inputs = item.querySelectorAll('.quantity, .unit-price, .tax-rate');
    inputs.forEach(input => {
        input.addEventListener('input', calculateTotals);
    });
}

function calculateTotals() {
    let subtotal = 0;
    let totalTax = 0;
    
    document.querySelectorAll('.line-item').forEach(item => {
        const quantity = parseFloat(item.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(item.querySelector('.unit-price').value) || 0;
        const taxRate = parseFloat(item.querySelector('.tax-rate').value) || 0;
        
        const lineTotal = quantity * unitPrice;
        const lineTax = lineTotal * (taxRate / 100);
        const totalWithTax = lineTotal + lineTax;
        
        item.querySelector('.line-total').value = totalWithTax.toFixed(2);
        
        subtotal += lineTotal;
        totalTax += lineTax;
    });
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = totalTax.toFixed(2);
    document.getElementById('totalAmount').textContent = (subtotal + totalTax).toFixed(2);
}

// Initialize event listeners on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.line-item').forEach(attachLineItemListeners);
    calculateTotals();
});
</script>
@endsection