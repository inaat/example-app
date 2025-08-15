@extends('layouts.app')

@section('title', 'Edit Certificate')
@section('page-title', 'Edit ZATCA Certificate')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Certificate Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('zatca.onboarding.update', $certificate) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="organization_identifier" class="form-label">Organization Identifier *</label>
                            <input type="text" class="form-control @error('organization_identifier') is-invalid @enderror" 
                                   id="organization_identifier" name="organization_identifier" 
                                   value="{{ old('organization_identifier', $certificate->organization_identifier) }}" required>
                            <div class="form-text">
                                <strong>Test VAT Numbers:</strong><br>
                                NonProduction/Simulation: 399999999900003<br>
                                Production: Use your actual VAT number
                            </div>
                            @error('organization_identifier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="organization_name" class="form-label">Organization Name *</label>
                            <input type="text" class="form-control @error('organization_name') is-invalid @enderror" 
                                   id="organization_name" name="organization_name" 
                                   value="{{ old('organization_name', $certificate->organization_name) }}" required>
                            @error('organization_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="organization_unit_name" class="form-label">Organization Unit</label>
                            <input type="text" class="form-control @error('organization_unit_name') is-invalid @enderror" 
                                   id="organization_unit_name" name="organization_unit_name" 
                                   value="{{ old('organization_unit_name', $certificate->organization_unit_name) }}">
                            @error('organization_unit_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="common_name" class="form-label">Common Name *</label>
                            <input type="text" class="form-control @error('common_name') is-invalid @enderror" 
                                   id="common_name" name="common_name" 
                                   value="{{ old('common_name', $certificate->common_name) }}" required>
                            @error('common_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="country_name" class="form-label">Country Code *</label>
                            <select class="form-select @error('country_name') is-invalid @enderror" 
                                    id="country_name" name="country_name" required>
                                <option value="SA" {{ old('country_name', $certificate->country_name) == 'SA' ? 'selected' : '' }}>SA - Saudi Arabia</option>
                            </select>
                            @error('country_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="environment_type" class="form-label">Environment Type *</label>
                            <select class="form-select @error('environment_type') is-invalid @enderror" 
                                    id="environment_type" name="environment_type" required onchange="handleEnvironmentChange()">
                                <option value="NonProduction" {{ old('environment_type', $certificate->environment_type) == 'NonProduction' ? 'selected' : '' }}>
                                    Non Production
                                </option>
                                <option value="Simulation" {{ old('environment_type', $certificate->environment_type) == 'Simulation' ? 'selected' : '' }}>
                                    Simulation
                                </option>
                                <option value="Production" {{ old('environment_type', $certificate->environment_type) == 'Production' ? 'selected' : '' }}>
                                    Production
                                </option>
                            </select>
                            @if($certificate->environment_type)
                                <div class="form-text text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Changing environment will reset CSR and CSID data!
                                </div>
                            @endif
                            @error('environment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location_address" class="form-label">Location Address</label>
                            <input type="text" class="form-control @error('location_address') is-invalid @enderror" 
                                   id="location_address" name="location_address" 
                                   value="{{ old('location_address', $certificate->location_address) }}">
                            @error('location_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="business_category" class="form-label">Business Category</label>
                            <input type="text" class="form-control @error('business_category') is-invalid @enderror" 
                                   id="business_category" name="business_category" 
                                   value="{{ old('business_category', $certificate->business_category) }}">
                            @error('business_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="otp_used" class="form-label">OTP (One-Time Password)</label>
                        <input type="text" class="form-control @error('otp_used') is-invalid @enderror" 
                               id="otp_used" name="otp_used" 
                               value="{{ old('otp_used', $certificate->otp_used) }}" 
                               placeholder="6-digit OTP from ZATCA portal">
                        <div class="form-text">
                            Required for CSID generation. Get from ZATCA portal for your environment.
                        </div>
                        @error('otp_used')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('zatca.onboarding.show', $certificate) }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Certificate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Edit Information
                </h6>
            </div>
            <div class="card-body">
                <h6>Current Status:</h6>
                <ul class="small">
                    <li>Environment: <strong>{{ $certificate->environment_type }}</strong></li>
                    <li>Status: <strong>{{ ucfirst($certificate->status) }}</strong></li>
                    <li>CSR: {{ $certificate->csr ? '✅ Generated' : '❌ Not generated' }}</li>
                    <li>Compliance CSID: {{ $certificate->ccsid_binarySecurityToken ? '✅ Obtained' : '❌ Not obtained' }}</li>
                    <li>Production CSID: {{ $certificate->pcsid_binarySecurityToken ? '✅ Obtained' : '❌ Not obtained' }}</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Environment Guide</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info small mb-3">
                    <h6>Environment Information:</h6>
                    <strong>NonProduction:</strong> For initial development and testing<br>
                    <strong>Simulation:</strong> For pre-production testing with ZATCA<br>
                    <strong>Production:</strong> For live invoice submission
                </div>

                <div class="alert alert-warning small">
                    <strong>Important:</strong> Changing the environment will reset all onboarding progress and you'll need to regenerate CSR and obtain new CSIDs.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleEnvironmentChange() {
    const currentEnv = '{{ $certificate->environment_type }}';
    const selectedEnv = document.getElementById('environment_type').value;
    
    if (currentEnv !== selectedEnv) {
        if (!confirm('Changing environment will reset all CSR and CSID data. Continue?')) {
            document.getElementById('environment_type').value = currentEnv;
        }
    }
}
</script>
@endsection