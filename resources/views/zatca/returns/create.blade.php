@extends('layouts.app')

@section('title', 'Create Return Invoice')
@section('page-title', 'Create Return Invoice (Credit Note)')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Return Invoice Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('zatca.returns.store') }}" method="POST" id="returnForm">
                    @csrf
                    
                    <!-- Return Information Alert -->
                    <div class="alert alert-warning mb-4" role="alert">
                        <h6 class="alert-heading"><i class="fas fa-undo me-2"></i>Creating Credit Note for Return/Refund</h6>
                        <p class="mb-0">This will create a Credit Note (381) to process customer returns, refunds, or invoice corrections. Credit notes reduce the customer's owed amount.</p>
                    </div>

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
                            <label for="invoice_subtype" class="form-label">Return Processing Type *</label>
                            <select class="form-select @error('invoice_subtype') is-invalid @enderror" 
                                    id="invoice_subtype" name="invoice_subtype" required>
                                <option value="01" {{ old('invoice_subtype', '02') == '01' ? 'selected' : '' }}>Standard (01) - Requires ZATCA clearance first</option>
                                <option value="02" {{ old('invoice_subtype', '02') == '02' ? 'selected' : '' }}>Simplified (02) - Process return, report within 24hrs</option>
                            </select>
                            @error('invoice_subtype')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Original Invoice Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Original Invoice Information</h6>
                        </div>
                        <div class="card-body">
                            @if($originalInvoice)
                                <input type="hidden" name="original_invoice_id" value="{{ $originalInvoice->id }}">
                                <div class="alert alert-info">
                                    <strong>Returning items from:</strong> {{ $originalInvoice->invoice_number }}<br>
                                    <strong>Original Date:</strong> {{ $originalInvoice->issue_date->format('M d, Y') }}<br>
                                    <strong>Original Amount:</strong> {{ number_format($originalInvoice->total_amount, 2) }} {{ $originalInvoice->currency }}
                                </div>
                                <input type="hidden" name="original_invoice_number" value="{{ $originalInvoice->invoice_number }}">
                            @else
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="original_invoice_number" class="form-label">Original Invoice Number *</label>
                                        <input type="text" class="form-control @error('original_invoice_number') is-invalid @enderror" 
                                               id="original_invoice_number" name="original_invoice_number" 
                                               value="{{ old('original_invoice_number') }}" required
                                               placeholder="e.g., INV-20250816-001">
                                        @error('original_invoice_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Return Details -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="return_reason" class="form-label">Return Reason *</label>
                            <select class="form-select @error('return_reason') is-invalid @enderror" 
                                    id="return_reason" name="return_reason" required>
                                <option value="">Select Reason</option>
                                <option value="Product defective" {{ old('return_reason') == 'Product defective' ? 'selected' : '' }}>Product Defective</option>
                                <option value="Customer changed mind" {{ old('return_reason') == 'Customer changed mind' ? 'selected' : '' }}>Customer Changed Mind</option>
                                <option value="Wrong item delivered" {{ old('return_reason') == 'Wrong item delivered' ? 'selected' : '' }}>Wrong Item Delivered</option>
                                <option value="Size/fit issue" {{ old('return_reason') == 'Size/fit issue' ? 'selected' : '' }}>Size/Fit Issue</option>
                                <option value="Price adjustment" {{ old('return_reason') == 'Price adjustment' ? 'selected' : '' }}>Price Adjustment</option>
                                <option value="Billing error" {{ old('return_reason') == 'Billing error' ? 'selected' : '' }}>Billing Error</option>
                                <option value="Service cancellation" {{ old('return_reason') == 'Service cancellation' ? 'selected' : '' }}>Service Cancellation</option>
                                <option value="Other" {{ old('return_reason') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('return_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="return_type" class="form-label">Return Type *</label>
                            <select class="form-select @error('return_type') is-invalid @enderror" 
                                    id="return_type" name="return_type" required>
                                <option value="partial" {{ old('return_type') == 'partial' ? 'selected' : '' }}>Partial Return</option>
                                <option value="full" {{ old('return_type') == 'full' ? 'selected' : '' }}>Full Return</option>
                            </select>
                            @error('return_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="issue_date" class="form-label">Return Date *</label>
                            <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                   id="issue_date" name="issue_date" 
                                   value="{{ old('issue_date', date('Y-m-d')) }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="issue_time" class="form-label">Return Time *</label>
                            <input type="time" class="form-control @error('issue_time') is-invalid @enderror" 
                                   id="issue_time" name="issue_time" 
                                   value="{{ old('issue_time', date('H:i')) }}" required>
                            @error('issue_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Buyer Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Customer Information</h6>
                        </div>
                        <div class="card-body">
                            @if($originalInvoice && $originalInvoice->buyer_info)
                                <div class="alert alert-info">
                                    <strong>Customer:</strong> {{ $originalInvoice->buyer_info['name'] ?? 'N/A' }}<br>
                                    <strong>VAT:</strong> {{ $originalInvoice->buyer_info['vat_number'] ?? 'N/A' }}
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="buyer_name" class="form-label">Customer Name</label>
                                        <input type="text" class="form-control" 
                                               id="buyer_name" name="buyer_info[name]" 
                                               value="{{ old('buyer_info.name') }}"
                                               placeholder="Customer name (optional for simplified)">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="buyer_vat" class="form-label">Customer VAT Number</label>
                                        <input type="text" class="form-control" 
                                               id="buyer_vat" name="buyer_info[vat_number]" 
                                               value="{{ old('buyer_info.vat_number') }}"
                                               placeholder="Customer VAT (optional for simplified)">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Return Items -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Return Items</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addReturnItem()">
                                <i class="fas fa-plus me-1"></i>Add Item
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="returnItems">
                                @if(old('line_items'))
                                    @foreach(old('line_items') as $index => $item)
                                        <div class="return-item border rounded p-3 mb-3">
                                            <div class="row">
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Item Name *</label>
                                                    <input type="text" class="form-control" 
                                                           name="line_items[{{ $index }}][name]" 
                                                           value="{{ $item['name'] }}" required>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Quantity *</label>
                                                    <input type="number" class="form-control" 
                                                           name="line_items[{{ $index }}][quantity]" 
                                                           value="{{ $item['quantity'] }}" 
                                                           step="0.01" min="0.01" required>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Unit Price *</label>
                                                    <input type="number" class="form-control" 
                                                           name="line_items[{{ $index }}][unit_price]" 
                                                           value="{{ $item['unit_price'] }}" 
                                                           step="0.01" min="0.01" required>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Tax Rate (%) *</label>
                                                    <select class="form-select" name="line_items[{{ $index }}][tax_rate]" required>
                                                        <option value="15" {{ $item['tax_rate'] == 15 ? 'selected' : '' }}>15%</option>
                                                        <option value="0" {{ $item['tax_rate'] == 0 ? 'selected' : '' }}>0%</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 mb-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger w-100" onclick="removeReturnItem(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Default first item -->
                                    <div class="return-item border rounded p-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Item Name *</label>
                                                <input type="text" class="form-control" name="line_items[0][name]" required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Quantity *</label>
                                                <input type="number" class="form-control" name="line_items[0][quantity]" 
                                                       step="0.01" min="0.01" required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Unit Price *</label>
                                                <input type="number" class="form-control" name="line_items[0][unit_price]" 
                                                       step="0.01" min="0.01" required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Tax Rate (%) *</label>
                                                <select class="form-select" name="line_items[0][tax_rate]" required>
                                                    <option value="15">15%</option>
                                                    <option value="0">0%</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger w-100" onclick="removeReturnItem(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="currency" value="SAR">

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('zatca.returns.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Returns
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Return Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let itemIndex = {{ old('line_items') ? count(old('line_items')) : 1 }};

function addReturnItem() {
    const container = document.getElementById('returnItems');
    const newItem = document.createElement('div');
    newItem.className = 'return-item border rounded p-3 mb-3';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label">Item Name *</label>
                <input type="text" class="form-control" name="line_items[${itemIndex}][name]" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Quantity *</label>
                <input type="number" class="form-control" name="line_items[${itemIndex}][quantity]" 
                       step="0.01" min="0.01" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Unit Price *</label>
                <input type="number" class="form-control" name="line_items[${itemIndex}][unit_price]" 
                       step="0.01" min="0.01" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Tax Rate (%) *</label>
                <select class="form-select" name="line_items[${itemIndex}][tax_rate]" required>
                    <option value="15">15%</option>
                    <option value="0">0%</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeReturnItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    itemIndex++;
}

function removeReturnItem(button) {
    const items = document.querySelectorAll('.return-item');
    if (items.length > 1) {
        button.closest('.return-item').remove();
    } else {
        alert('At least one item is required for the return.');
    }
}
</script>
@endsection