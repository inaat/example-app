@extends('layouts.app')

@section('title', 'Add Product')
@section('page-title', 'Add New Product')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                   id="sku" name="sku" value="{{ old('sku') }}"
                                   placeholder="e.g. PRD-001">
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Stock Keeping Unit (optional)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="unit_price" class="form-label">Unit Price (SAR) *</label>
                            <input type="number" class="form-control @error('unit_price') is-invalid @enderror" 
                                   id="unit_price" name="unit_price" value="{{ old('unit_price') }}" 
                                   min="0" step="0.01" required>
                            @error('unit_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate (%) *</label>
                            <select class="form-select @error('tax_rate') is-invalid @enderror" 
                                    id="tax_rate" name="tax_rate" required>
                                <option value="">Select Tax Rate</option>
                                <option value="0" {{ old('tax_rate') == '0' ? 'selected' : '' }}>0% - Exempt</option>
                                <option value="5" {{ old('tax_rate') == '5' ? 'selected' : '' }}>5% - Reduced Rate</option>
                                <option value="15" {{ old('tax_rate', '15') == '15' ? 'selected' : '' }}>15% - Standard Rate</option>
                            </select>
                            @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="unit_of_measure" class="form-label">Unit of Measure *</label>
                            <select class="form-select @error('unit_of_measure') is-invalid @enderror" 
                                    id="unit_of_measure" name="unit_of_measure" required>
                                <option value="">Select Unit</option>
                                <option value="PCE" {{ old('unit_of_measure', 'PCE') == 'PCE' ? 'selected' : '' }}>PCE - Piece</option>
                                <option value="KG" {{ old('unit_of_measure') == 'KG' ? 'selected' : '' }}>KG - Kilogram</option>
                                <option value="L" {{ old('unit_of_measure') == 'L' ? 'selected' : '' }}>L - Liter</option>
                                <option value="M" {{ old('unit_of_measure') == 'M' ? 'selected' : '' }}>M - Meter</option>
                                <option value="M2" {{ old('unit_of_measure') == 'M2' ? 'selected' : '' }}>M2 - Square Meter</option>
                                <option value="HR" {{ old('unit_of_measure') == 'HR' ? 'selected' : '' }}>HR - Hour</option>
                                <option value="SET" {{ old('unit_of_measure') == 'SET' ? 'selected' : '' }}>SET - Set</option>
                            </select>
                            @error('unit_of_measure')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active (Available for invoices)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Product
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
                    <i class="fas fa-info-circle me-2"></i>Product Guidelines
                </h6>
            </div>
            <div class="card-body">
                <h6>Naming</h6>
                <ul class="small">
                    <li>Use clear, descriptive names</li>
                    <li>Include model/variant if applicable</li>
                    <li>Avoid special characters</li>
                </ul>

                <h6>Pricing</h6>
                <ul class="small">
                    <li>Set competitive unit prices</li>
                    <li>Consider bulk pricing separately</li>
                    <li>Review prices regularly</li>
                </ul>

                <h6>Tax Rates</h6>
                <ul class="small">
                    <li><strong>15%:</strong> Standard VAT rate</li>
                    <li><strong>5%:</strong> Reduced rate (food, etc.)</li>
                    <li><strong>0%:</strong> Exempt items</li>
                </ul>

                <h6>Units</h6>
                <ul class="small">
                    <li><strong>PCE:</strong> Individual items</li>
                    <li><strong>KG:</strong> Weight-based</li>
                    <li><strong>L:</strong> Volume-based</li>
                    <li><strong>HR:</strong> Time-based services</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection