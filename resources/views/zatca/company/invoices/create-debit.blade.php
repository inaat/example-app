@extends('layouts.app')

@section('title', 'Create Debit Note')
@section('page-title', 'Create Debit Note for ' . $invoice->invoice_number)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Debit Note Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('zatca.company.invoices.store') }}" method="POST" id="debitForm">
                    @csrf
                    
                    <!-- Debit Information Alert -->
                    <div class="alert alert-success mb-4" role="alert">
                        <h6 class="alert-heading"><i class="fas fa-plus-circle me-2"></i>Creating Debit Note for Additional Charges</h6>
                        <p class="mb-0">This will create a Debit Note (381) to add additional charges, fees, or corrections that increase the customer's owed amount.</p>
                    </div>

                    <!-- Original Invoice Reference -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Original Invoice Reference</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                                    <p><strong>Issue Date:</strong> {{ $invoice->issue_date->format('Y-m-d') }}</p>
                                    <p><strong>Total Amount:</strong> {{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Customer:</strong> {{ $invoice->buyer_info['name'] ?? 'No customer' }}</p>
                                    <p><strong>Company:</strong> {{ $invoice->company->organization_name }}</p>
                                    <p><strong>Type:</strong> {{ $invoice->getInvoiceTypeName() }} ({{ $invoice->invoice_subtype === '01' ? 'Standard' : 'Simplified' }})</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden fields for original invoice reference -->
                    <input type="hidden" name="original_invoice_id" value="{{ $invoice->id }}">
                    <input type="hidden" name="original_invoice_number" value="{{ $invoice->invoice_number }}">
                    <input type="hidden" name="invoice_type" value="381">
                    <input type="hidden" name="company_zatca_onboarding_id" value="{{ $invoice->company_zatca_onboarding_id }}">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="invoice_number" class="form-label">Debit Note Number *</label>
                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                   id="invoice_number" name="invoice_number" 
                                   value="{{ old('invoice_number', 'DB-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)) }}" required>
                            @error('invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="invoice_subtype" class="form-label">Processing Type *</label>
                            <select class="form-select @error('invoice_subtype') is-invalid @enderror" 
                                    id="invoice_subtype" name="invoice_subtype" required>
                                <option value="01" {{ old('invoice_subtype', $invoice->invoice_subtype) == '01' ? 'selected' : '' }}>Standard (01) - Requires ZATCA clearance first</option>
                                <option value="02" {{ old('invoice_subtype', $invoice->invoice_subtype) == '02' ? 'selected' : '' }}>Simplified (02) - Issue first, report within 24hrs</option>
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
                        <label for="return_reason" class="form-label">Reason for Additional Charges *</label>
                        <select class="form-select @error('return_reason') is-invalid @enderror" 
                                id="return_reason" name="return_reason" required>
                            <option value="">Select Reason</option>
                            <option value="Late payment fee" {{ old('return_reason') == 'Late payment fee' ? 'selected' : '' }}>Late payment fee</option>
                            <option value="Processing fee" {{ old('return_reason') == 'Processing fee' ? 'selected' : '' }}>Processing fee</option>
                            <option value="Additional services" {{ old('return_reason') == 'Additional services' ? 'selected' : '' }}>Additional services</option>
                            <option value="Shipping charges" {{ old('return_reason') == 'Shipping charges' ? 'selected' : '' }}>Shipping charges</option>
                            <option value="Insurance fee" {{ old('return_reason') == 'Insurance fee' ? 'selected' : '' }}>Insurance fee</option>
                            <option value="Billing correction" {{ old('return_reason') == 'Billing correction' ? 'selected' : '' }}>Billing correction</option>
                            <option value="Other charges" {{ old('return_reason') == 'Other charges' ? 'selected' : '' }}>Other charges</option>
                        </select>
                        @error('return_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Copy customer information from original invoice -->
                    @if($invoice->buyer_info)
                    <input type="hidden" name="buyer_name" value="{{ $invoice->buyer_info['name'] }}">
                    <input type="hidden" name="buyer_vat" value="{{ $invoice->buyer_info['vat_number'] ?? '' }}">
                    <input type="hidden" name="buyer_address" value="{{ $invoice->buyer_info['address'] ?? '' }}">
                    @endif

                    <input type="hidden" name="currency" value="{{ $invoice->currency }}">

                    <!-- Line Items -->
                    <h6 class="border-bottom pb-2 mb-3">Additional Charges</h6>
                    
                    <div id="lineItems">
                        <div class="line-item border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Description *</label>
                                    <input type="text" class="form-control" name="line_items[0][name]" 
                                           value="{{ old('line_items.0.name', 'Additional charge') }}" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Quantity *</label>
                                    <input type="number" class="form-control quantity" name="line_items[0][quantity]" 
                                           value="{{ old('line_items.0.quantity', '1') }}" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Unit Price *</label>
                                    <input type="number" class="form-control unit-price" name="line_items[0][unit_price]" 
                                           value="{{ old('line_items.0.unit_price', '0') }}" min="0" step="0.01" required>
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
                            <i class="fas fa-plus me-2"></i>Add Additional Charge
                        </button>
                    </div>

                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-end" id="subtotal">0.00</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tax:</strong></td>
                                        <td class="text-end" id="taxAmount">0.00</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td><strong>Total Additional Amount:</strong></td>
                                        <td class="text-end"><strong id="totalAmount">0.00</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('zatca.company.invoices.show', $invoice) }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus-circle me-2"></i>Create Debit Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let itemIndex = 1;

function addLineItem() {
    const container = document.getElementById('lineItems');
    const newItem = document.createElement('div');
    newItem.className = 'line-item border rounded p-3 mb-3';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label">Description *</label>
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