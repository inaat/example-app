@extends('layouts.app')

@section('title', 'Create Certificate')
@section('page-title', 'Create ZATCA Certificate')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Certificate Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('zatca.onboarding.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="organization_identifier" class="form-label">VAT Number *</label>
                        <input type="text" class="form-control @error('organization_identifier') is-invalid @enderror" 
                               id="organization_identifier" name="organization_identifier" 
                               value="{{ old('organization_identifier', '399999999900003') }}" required>
                        @error('organization_identifier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="organization_name" class="form-label">Company Name *</label>
                        <input type="text" class="form-control @error('organization_name') is-invalid @enderror" 
                               id="organization_name" name="organization_name" 
                               value="{{ old('organization_name', 'Maximum Speed Tech Supply LTD') }}" required>
                        @error('organization_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    
                    <div class="mb-3">
                        <label for="organization_unit_name" class="form-label">Branch/Department</label>
                        <input type="text" class="form-control @error('organization_unit_name') is-invalid @enderror" 
                               id="organization_unit_name" name="organization_unit_name" 
                               value="{{ old('organization_unit_name', 'Riyadh Branch') }}">
                        @error('organization_unit_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="location_address" class="form-label">Location Address</label>
                        <input type="text" class="form-control @error('location_address') is-invalid @enderror" 
                               id="location_address" name="location_address" 
                               value="{{ old('location_address', 'Riyadh, Saudi Arabia') }}">
                        @error('location_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="environment_type" class="form-label">Environment Type *</label>
                        <select class="form-select @error('environment_type') is-invalid @enderror" 
                                id="environment_type" name="environment_type" required>
                            <option value="NonProduction" {{ old('environment_type', 'NonProduction') == 'NonProduction' ? 'selected' : '' }}>
                                Non Production
                            </option>
                            <option value="Simulation" {{ old('environment_type') == 'Simulation' ? 'selected' : '' }}>
                                Simulation
                            </option>
                            <option value="Production" {{ old('environment_type') == 'Production' ? 'selected' : '' }}>
                                Production
                            </option>
                        </select>
                        @error('environment_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="business_category" class="form-label">Business Category</label>
                        <input type="text" class="form-control @error('business_category') is-invalid @enderror" 
                               id="business_category" name="business_category" 
                               value="{{ old('business_category', 'Supply activities') }}">
                        @error('business_category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="otp_used" class="form-label">ZATCA OTP</label>
                        <input type="text" class="form-control @error('otp_used') is-invalid @enderror" 
                               id="otp_used" name="otp_used" 
                               value="{{ old('otp_used') }}"
                               placeholder="Enter OTP from ZATCA portal">
                        @error('otp_used')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Required for production CSID generation</div>
                    </div>

                    <!-- Hidden fields for auto-generated values -->
                    <input type="hidden" name="common_name" id="common_name_hidden" value="">
                    <input type="hidden" name="country_name" value="SA">

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('zatca.onboarding.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Certificate
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
                    <i class="fas fa-info-circle me-2"></i>Information
                </h6>
            </div>
            <div class="card-body">
                <p class="small">This form creates a new ZATCA certificate entry for onboarding process.</p>
                
                <h6>Next Steps:</h6>
                <ol class="small">
                    <li>Generate CSR (Certificate Signing Request)</li>
                    <li>Obtain Compliance CSID using OTP</li>
                    <li>Submit test invoices for compliance</li>
                    <li>Obtain Production CSID</li>
                    <li>Start production invoice processing</li>
                </ol>
                
                <div class="alert alert-info small mb-3">
                    <h6>Environment Information:</h6>
                    <strong>NonProduction:</strong> For initial development and testing<br>
                    <strong>Simulation:</strong> For pre-production testing with ZATCA<br>
                    <strong>Production:</strong> For live invoice submission
                </div>

                <div class="alert alert-warning small">
                    <strong>Note:</strong> Ensure you have the correct OTP from ZATCA portal before proceeding to CSID generation.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateAutoGeneratedFields() {
    const environment = document.getElementById('environment_type').value;
    const vatNumber = document.getElementById('organization_identifier').value;
    
    // Generate common name based on environment and VAT number
    let prefix = 'TST'; // Default
    if (environment === 'Production') {
        prefix = 'PRD';
    } else if (environment === 'Simulation') {
        prefix = 'SIM';
    }
    
    // For production, use commercial registration from real data
    const commercialReg = environment === 'Production' ? '1009191090' : '886431145';
    const commonName = `${prefix}-${commercialReg}-${vatNumber}`;
    document.getElementById('common_name_hidden').value = commonName;
}

// Update fields when relevant inputs change
document.addEventListener('DOMContentLoaded', function() {
    const fieldsToWatch = ['environment_type', 'organization_identifier'];
    
    fieldsToWatch.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.addEventListener('input', updateAutoGeneratedFields);
            element.addEventListener('change', updateAutoGeneratedFields);
        }
    });
    
    // Initial generation
    updateAutoGeneratedFields();
});
</script>
@endsection