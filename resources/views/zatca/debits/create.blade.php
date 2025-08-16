@extends('layouts.app')

@section('title', 'Create Debit Note')
@section('page-title', 'Create Debit Note (Additional Charges)')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Debit Note Information</h5>
                @if(isset($originalInvoice))
                    <small class="text-muted">Adding charges to Invoice: {{ $originalInvoice->invoice_number }}</small>
                @endif
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('zatca.debits.store') }}">
                    @csrf
                    
                    @if(isset($originalInvoice))
                        <input type="hidden" name="original_invoice_id" value="{{ $originalInvoice->id }}">
                        <input type="hidden" name="original_invoice_number" value="{{ $originalInvoice->invoice_number }}">
                        <input type="hidden" name="certificate_info_id" value="{{ $originalInvoice->certificate_info_id }}">
                        <input type="hidden" name="currency" value="{{ $originalInvoice->currency }}">
                        <input type="hidden" name="invoice_subtype" value="{{ $originalInvoice->invoice_subtype }}">
                        
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Original Invoice:</strong> {{ $originalInvoice->invoice_number }} 
                            ({{ $originalInvoice->buyer_info['name'] ?? 'N/A' }}) - 
                            {{ number_format($originalInvoice->total_amount, 2) }} {{ $originalInvoice->currency }}
                        </div>
                    @endif

                    <div class="row">
                        @if(!isset($originalInvoice))
                        <div class="col-md-6 mb-3">
                            <label for="certificate_info_id" class="form-label">Certificate *</label>
                            <select class="form-select @error('certificate_info_id') is-invalid @enderror" 
                                    id="certificate_info_id" name="certificate_info_id" required>
                                <option value="">Select Certificate</option>
                                @foreach($certificates as $certificate)
                                    <option value="{{ $certificate->id }}" {{ old('certificate_info_id') == $certificate->id ? 'selected' : '' }}>
                                        {{ $certificate->organization_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('certificate_info_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="original_invoice_number" class="form-label">Original Invoice Number *</label>
                            <input type="text" class="form-control @error('original_invoice_number') is-invalid @enderror" 
                                   id="original_invoice_number" name="original_invoice_number" 
                                   value="{{ old('original_invoice_number') }}" required>
                            @error('original_invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label for="debit_reason" class="form-label">Reason for Additional Charges *</label>
                            <input type="text" class="form-control @error('debit_reason') is-invalid @enderror" 
                                   id="debit_reason" name="debit_reason" 
                                   value="{{ old('debit_reason') }}" 
                                   placeholder="e.g., Additional shipping costs, Late payment fee" required>
                            @error('debit_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="debit_type" class="form-label">Type of Additional Charge *</label>
                            <select class="form-select @error('debit_type') is-invalid @enderror" 
                                    id="debit_type" name="debit_type" required>
                                <option value="">Select Type</option>
                                <option value="additional_charges" {{ old('debit_type') == 'additional_charges' ? 'selected' : '' }}>Additional Charges</option>
                                <option value="price_correction" {{ old('debit_type') == 'price_correction' ? 'selected' : '' }}>Price Correction</option>
                                <option value="extra_services" {{ old('debit_type') == 'extra_services' ? 'selected' : '' }}>Extra Services</option>
                            </select>
                            @error('debit_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small>
                                    <strong>Additional Charges:</strong> Shipping, fees, penalties | 
                                    <strong>Price Correction:</strong> Original price was too low | 
                                    <strong>Extra Services:</strong> Additional work performed
                                </small>
                            </div>
                        </div>

                        @if(!isset($originalInvoice))
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

                        <div class="col-md-6 mb-3">
                            <label for="currency" class="form-label">Currency *</label>
                            <select class="form-select @error('currency') is-invalid @enderror" 
                                    id="currency" name="currency" required>
                                <option value="SAR" {{ old('currency', 'SAR') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

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
                                   value="{{ old('issue_time', date('H:i:s')) }}" required>
                            @error('issue_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Line Items Section -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Additional Charges/Items</h6>
                        </div>
                        <div class="card-body">
                            <div id="line-items-container">
                                @if(old('line_items'))
                                    @foreach(old('line_items') as $index => $item)
                                        <div class="line-item border p-3 mb-3 rounded">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Item/Service Name *</label>
                                                    <input type="text" name="line_items[{{ $index }}][name]" 
                                                           class="form-control" value="{{ $item['name'] ?? '' }}" required>
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Quantity *</label>
                                                    <input type="number" name="line_items[{{ $index }}][quantity]" 
                                                           class="form-control" value="{{ $item['quantity'] ?? '1' }}" 
                                                           min="0.01" step="0.01" required>
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Unit Price *</label>
                                                    <input type="number" name="line_items[{{ $index }}][unit_price]" 
                                                           class="form-control" value="{{ $item['unit_price'] ?? '' }}" 
                                                           min="0.01" step="0.01" required>
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Tax Rate (%) *</label>
                                                    <input type="number" name="line_items[{{ $index }}][tax_rate]" 
                                                           class="form-control" value="{{ $item['tax_rate'] ?? '15' }}" 
                                                           min="0" step="0.01" required>
                                                </div>
                                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger remove-item">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="line-item border p-3 mb-3 rounded">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Item/Service Name *</label>
                                                <input type="text" name="line_items[0][name]" 
                                                       class="form-control" placeholder="e.g., Additional shipping" required>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Quantity *</label>
                                                <input type="number" name="line_items[0][quantity]" 
                                                       class="form-control" value="1" min="0.01" step="0.01" required>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Unit Price *</label>
                                                <input type="number" name="line_items[0][unit_price]" 
                                                       class="form-control" min="0.01" step="0.01" required>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Tax Rate (%) *</label>
                                                <input type="number" name="line_items[0][tax_rate]" 
                                                       class="form-control" value="15" min="0" step="0.01" required>
                                            </div>
                                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger remove-item">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <button type="button" id="add-item" class="btn btn-outline-primary">
                                <i class="fas fa-plus"></i> Add Another Item
                            </button>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus-circle me-2"></i>Create Debit Note
                            </button>
                            <a href="{{ route('zatca.debits.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let itemIndex = {{ old('line_items') ? count(old('line_items')) : 1 }};
    
    // Add new line item
    $('#add-item').click(function() {
        const newItem = `
            <div class="line-item border p-3 mb-3 rounded">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Item/Service Name *</label>
                        <input type="text" name="line_items[${itemIndex}][name]" 
                               class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="line_items[${itemIndex}][quantity]" 
                               class="form-control" value="1" min="0.01" step="0.01" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Unit Price *</label>
                        <input type="number" name="line_items[${itemIndex}][unit_price]" 
                               class="form-control" min="0.01" step="0.01" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Tax Rate (%) *</label>
                        <input type="number" name="line_items[${itemIndex}][tax_rate]" 
                               class="form-control" value="15" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger remove-item">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#line-items-container').append(newItem);
        itemIndex++;
    });
    
    // Remove line item
    $(document).on('click', '.remove-item', function() {
        if ($('.line-item').length > 1) {
            $(this).closest('.line-item').remove();
        } else {
            alert('At least one item is required.');
        }
    });
});
</script>
@endsection