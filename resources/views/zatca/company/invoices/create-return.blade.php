@extends('layouts.app')

@section('title', 'Create Return Invoice')
@section('page-title', 'Create Return Invoice (Credit Note) for ' . $invoice->invoice_number)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Return Invoice Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('zatca.company.returns.store') }}" method="POST" id="returnForm">
                    @csrf
                    
                    <!-- Return Information Alert -->
                    <div class="alert alert-warning mb-4" role="alert">
                        <h6 class="alert-heading"><i class="fas fa-undo me-2"></i>Creating Credit Note for Return/Refund</h6>
                        <p class="mb-0">This will create a Credit Note (383) to process customer returns, refunds, or invoice corrections. Credit notes reduce the customer's owed amount.</p>
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
                                    <p><strong>Customer:</strong> {{ $invoice->customer->name ?? 'No customer' }}</p>
                                    <p><strong>Company:</strong> {{ $invoice->company->organization_name }}</p>
                                    <p><strong>Type:</strong> {{ $invoice->getInvoiceTypeName() }} ({{ $invoice->invoice_subtype === '01' ? 'Standard' : 'Simplified' }})</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden fields for original invoice reference -->
                    <input type="hidden" name="original_invoice_id" value="{{ $invoice->id }}">
                    <input type="hidden" name="original_invoice_number" value="{{ $invoice->invoice_number }}">
                    <input type="hidden" name="invoice_type" value="383">
                    <input type="hidden" name="company_zatca_onboarding_id" value="{{ $invoice->company_zatca_onboarding_id }}">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="invoice_number" class="form-label">Credit Note Number *</label>
                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                   id="invoice_number" name="invoice_number" 
                                   value="{{ old('invoice_number', 'CR-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)) }}" required>
                            @error('invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="invoice_subtype" class="form-label">Return Processing Type *</label>
                            <select class="form-select @error('invoice_subtype') is-invalid @enderror" 
                                    id="invoice_subtype" name="invoice_subtype" required>
                                <option value="01" {{ old('invoice_subtype', $invoice->invoice_subtype) == '01' ? 'selected' : '' }}>Standard (01) - Requires ZATCA clearance first</option>
                                <option value="02" {{ old('invoice_subtype', $invoice->invoice_subtype) == '02' ? 'selected' : '' }}>Simplified (02) - Process return, report within 24hrs</option>
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
                        <label for="return_reason" class="form-label">Return Reason *</label>
                        <select class="form-select @error('return_reason') is-invalid @enderror" 
                                id="return_reason" name="return_reason" required>
                            <option value="">Select Return Reason</option>
                            <option value="Defective product" {{ old('return_reason') == 'Defective product' ? 'selected' : '' }}>Defective product</option>
                            <option value="Wrong item shipped" {{ old('return_reason') == 'Wrong item shipped' ? 'selected' : '' }}>Wrong item shipped</option>
                            <option value="Customer changed mind" {{ old('return_reason') == 'Customer changed mind' ? 'selected' : '' }}>Customer changed mind</option>
                            <option value="Billing error" {{ old('return_reason') == 'Billing error' ? 'selected' : '' }}>Billing error</option>
                            <option value="Duplicate charge" {{ old('return_reason') == 'Duplicate charge' ? 'selected' : '' }}>Duplicate charge</option>
                            <option value="Service not provided" {{ old('return_reason') == 'Service not provided' ? 'selected' : '' }}>Service not provided</option>
                            <option value="Other" {{ old('return_reason') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('return_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Customer Selection -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Customer Information / معلومات العميل</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="customer_id" class="form-label">Select Customer *</label>
                                        <select class="form-select @error('customer_id') is-invalid @enderror" 
                                                id="customer_id" name="customer_id" required>
                                            <option value="">Choose a customer...</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" 
                                                        data-name="{{ $customer->name }}"
                                                        data-vat="{{ $customer->vat_number }}"
                                                        data-email="{{ $customer->email }}"
                                                        data-phone="{{ $customer->phone }}"
                                                        data-address="{{ $customer->full_address }}"
                                                        {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} @if($customer->vat_number) ({{ $customer->vat_number }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('customer_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Return will be processed for the selected customer.</div>
                                    </div>

                                    <!-- Customer Details Display -->
                                    <div id="customerDetails" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Customer Name:</strong> <span id="selectedCustomerName"></span></p>
                                                <p><strong>VAT Number:</strong> <span id="selectedCustomerVat"></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Email:</strong> <span id="selectedCustomerEmail"></span></p>
                                                <p><strong>Phone:</strong> <span id="selectedCustomerPhone"></span></p>
                                            </div>
                                        </div>
                                        <p><strong>Address:</strong> <span id="selectedCustomerAddress"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="currency" value="{{ $invoice->currency }}">

                    <!-- Line Items -->
                    <h6 class="border-bottom pb-2 mb-3">Return Items</h6>
                    
                    <div id="lineItems">
                        @foreach($invoice->line_items as $index => $item)
                        <div class="line-item border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Item Name *</label>
                                    <input type="text" class="form-control" name="line_items[{{ $index }}][name]" 
                                           value="{{ $item['name'] }}" required readonly>
                                    <input type="hidden" name="line_items[{{ $index }}][product_id]" value="{{ $item['product_id'] }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Original Qty</label>
                                    <input type="number" class="form-control" value="{{ $item['quantity'] }}" readonly>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Return Qty *</label>
                                    <input type="number" class="form-control quantity" name="line_items[{{ $index }}][quantity]" 
                                           value="0" min="0" max="{{ $item['quantity'] }}" step="0.01" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Unit Price *</label>
                                    <input type="number" class="form-control unit-price" name="line_items[{{ $index }}][unit_price]" 
                                           value="{{ $item['unit_price'] }}" min="0" step="0.01" required readonly>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Tax Rate (%) *</label>
                                    <input type="number" class="form-control tax-rate" name="line_items[{{ $index }}][tax_rate]" 
                                           value="{{ $item['tax_rate'] }}" min="0" step="0.01" required readonly>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input item-checkbox" type="checkbox" id="include_{{ $index }}">
                                        <label class="form-check-label" for="include_{{ $index }}">
                                            Include this item in return
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Return Subtotal:</strong></td>
                                        <td class="text-end" id="subtotal">0.00</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Return Tax:</strong></td>
                                        <td class="text-end" id="taxAmount">0.00</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td><strong>Total Return Amount:</strong></td>
                                        <td class="text-end"><strong id="totalAmount">0.00</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('zatca.company.invoices.show', $invoice) }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-undo me-2"></i>Create Return Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function calculateTotals() {
    let subtotal = 0;
    let totalTax = 0;
    
    document.querySelectorAll('.line-item').forEach(item => {
        const checkbox = item.querySelector('.item-checkbox');
        if (!checkbox.checked) return;
        
        const quantity = parseFloat(item.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(item.querySelector('.unit-price').value) || 0;
        const taxRate = parseFloat(item.querySelector('.tax-rate').value) || 0;
        
        const lineTotal = quantity * unitPrice;
        const lineTax = lineTotal * (taxRate / 100);
        
        subtotal += lineTotal;
        totalTax += lineTax;
    });
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = totalTax.toFixed(2);
    document.getElementById('totalAmount').textContent = (subtotal + totalTax).toFixed(2);
}

function updateCustomerDetails() {
    const customerSelect = document.getElementById('customer_id');
    const customerDetails = document.getElementById('customerDetails');
    
    if (customerSelect.value) {
        const selectedOption = customerSelect.options[customerSelect.selectedIndex];
        
        document.getElementById('selectedCustomerName').textContent = selectedOption.dataset.name || '';
        document.getElementById('selectedCustomerVat').textContent = selectedOption.dataset.vat || 'N/A';
        document.getElementById('selectedCustomerEmail').textContent = selectedOption.dataset.email || 'N/A';
        document.getElementById('selectedCustomerPhone').textContent = selectedOption.dataset.phone || 'N/A';
        document.getElementById('selectedCustomerAddress').textContent = selectedOption.dataset.address || 'N/A';
        
        customerDetails.style.display = 'block';
    } else {
        customerDetails.style.display = 'none';
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quantity, .item-checkbox').forEach(input => {
        input.addEventListener('change', calculateTotals);
    });
    
    // Customer selection change handler
    document.getElementById('customer_id').addEventListener('change', updateCustomerDetails);
    
    // Initially disable all quantity inputs
    document.querySelectorAll('.quantity').forEach(input => {
        input.disabled = true;
    });
    
    // Enable/disable quantity inputs based on checkbox
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const lineItem = this.closest('.line-item');
            const quantityInput = lineItem.querySelector('.quantity');
            quantityInput.disabled = !this.checked;
            if (!this.checked) {
                quantityInput.value = 0;
            }
            calculateTotals();
        });
    });
    
    // Initialize customer details on page load
    updateCustomerDetails();
});
</script>
@endsection