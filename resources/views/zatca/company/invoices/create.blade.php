@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Create Company Invoice</h4>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('zatca.company.invoices.store') }}">
                        @csrf

                        {{-- Company Selection --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="company_zatca_onboarding_id" class="form-label">Company *</label>
                                <select name="company_zatca_onboarding_id" id="company_zatca_onboarding_id" class="form-control" required>
                                    <option value="">Select Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_zatca_onboarding_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->organization_name }} ({{ $company->vat_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Basic Invoice Information --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="invoice_number" class="form-label">Invoice Number *</label>
                                <input type="text" name="invoice_number" id="invoice_number" class="form-control" 
                                       value="{{ old('invoice_number', 'INV-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="invoice_type" class="form-label">Invoice Type *</label>
                                <select name="invoice_type" id="invoice_type" class="form-control" required>
                                    <option value="388" {{ old('invoice_type') == '388' ? 'selected' : '' }}>Invoice (388)</option>
                                    <option value="381" {{ old('invoice_type') == '381' ? 'selected' : '' }}>Debit Note (381)</option>
                                    <option value="383" {{ old('invoice_type') == '383' ? 'selected' : '' }}>Credit Note (383)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="invoice_subtype" class="form-label">Invoice Subtype *</label>
                                <select name="invoice_subtype" id="invoice_subtype" class="form-control" required>
                                    <option value="01" {{ old('invoice_subtype') == '01' ? 'selected' : '' }}>Standard (01)</option>
                                    <option value="02" {{ old('invoice_subtype') == '02' ? 'selected' : '' }}>Simplified (02)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Dates and Times --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="issue_date" class="form-label">Issue Date *</label>
                                <input type="date" name="issue_date" id="issue_date" class="form-control" 
                                       value="{{ old('issue_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="issue_time" class="form-label">Issue Time *</label>
                                <input type="time" name="issue_time" id="issue_time" class="form-control" 
                                       value="{{ old('issue_time', date('H:i:s')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" 
                                       value="{{ old('due_date') }}">
                            </div>
                        </div>

                        {{-- Buyer Information --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Buyer Information (Optional for Simplified Invoices)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="buyer_name" class="form-label">Buyer Name</label>
                                        <input type="text" name="buyer_name" id="buyer_name" class="form-control" 
                                               value="{{ old('buyer_name') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="buyer_vat" class="form-label">Buyer VAT Number</label>
                                        <input type="text" name="buyer_vat" id="buyer_vat" class="form-control" 
                                               value="{{ old('buyer_vat') }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="buyer_address" class="form-label">Buyer Address</label>
                                        <textarea name="buyer_address" id="buyer_address" class="form-control" rows="2">{{ old('buyer_address') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Currency --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="currency" class="form-label">Currency *</label>
                                <select name="currency" id="currency" class="form-control" required>
                                    <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR - Saudi Riyal</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                </select>
                            </div>
                        </div>

                        {{-- Line Items --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Invoice Items</h6>
                            </div>
                            <div class="card-body">
                                <div id="lineItems">
                                    <div class="line-item border rounded p-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Select Product *</label>
                                                <select class="form-control product-select" name="line_items[0][product_id]" required>
                                                    <option value="">Choose Product</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" 
                                                                data-price="{{ $product->unit_price }}"
                                                                data-tax="{{ $product->tax_rate }}"
                                                                data-name="{{ $product->name }}"
                                                                data-unit="{{ $product->unit_of_measure }}"
                                                                {{ old('line_items.0.product_id') == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }} ({{ number_format($product->unit_price, 2) }} SAR)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Quantity *</label>
                                                <input type="number" class="form-control quantity" name="line_items[0][quantity]" 
                                                       value="{{ old('line_items.0.quantity', '1') }}" min="0" step="0.01" required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Unit Price *</label>
                                                <input type="number" class="form-control unit-price" name="line_items[0][unit_price]" 
                                                       value="{{ old('line_items.0.unit_price', '100') }}" min="0" step="0.01" required readonly>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Tax Rate (%) *</label>
                                                <input type="number" class="form-control tax-rate" name="line_items[0][tax_rate]" 
                                                       value="{{ old('line_items.0.tax_rate', '15') }}" min="0" max="100" step="0.01" required readonly>
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
                                                    <td><strong>Total:</strong></td>
                                                    <td class="text-end"><strong id="totalAmount">0.00</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('zatca.company.invoices.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Invoice</button>
                        </div>
                    </form>
                </div>
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
                <label class="form-label">Select Product *</label>
                <select class="form-control product-select" name="line_items[${itemIndex}][product_id]" required>
                    <option value="">Choose Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" 
                                data-price="{{ $product->unit_price }}"
                                data-tax="{{ $product->tax_rate }}"
                                data-name="{{ $product->name }}"
                                data-unit="{{ $product->unit_of_measure }}">
                            {{ $product->name }} ({{ number_format($product->unit_price, 2) }} SAR)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Quantity *</label>
                <input type="number" class="form-control quantity" name="line_items[${itemIndex}][quantity]" 
                       value="1" min="0" step="0.01" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Unit Price *</label>
                <input type="number" class="form-control unit-price" name="line_items[${itemIndex}][unit_price]" 
                       value="0" min="0" step="0.01" required readonly>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Tax Rate (%) *</label>
                <input type="number" class="form-control tax-rate" name="line_items[${itemIndex}][tax_rate]" 
                       value="15" min="0" max="100" step="0.01" required readonly>
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
    attachProductSelectListener(newItem);
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

function attachProductSelectListener(item) {
    const productSelect = item.querySelector('.product-select');
    productSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            const unitPriceInput = item.querySelector('.unit-price');
            const taxRateInput = item.querySelector('.tax-rate');
            
            unitPriceInput.value = option.getAttribute('data-price');
            taxRateInput.value = option.getAttribute('data-tax');
            
            calculateTotals();
        } else {
            item.querySelector('.unit-price').value = '0';
            item.querySelector('.tax-rate').value = '15';
            calculateTotals();
        }
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
    document.querySelectorAll('.line-item').forEach(function(item) {
        attachLineItemListeners(item);
        attachProductSelectListener(item);
    });
    calculateTotals();
});
</script>
@endsection