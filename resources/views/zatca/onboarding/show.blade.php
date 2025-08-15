@extends('layouts.app')

@section('title', 'Certificate Details')
@section('page-title', 'Certificate: ' . $certificate->organization_name)

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Certificate Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Certificate Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Organization:</dt>
                            <dd class="col-sm-7">{{ $certificate->organization_name }}</dd>
                            
                            <dt class="col-sm-5">Identifier:</dt>
                            <dd class="col-sm-7">{{ $certificate->organization_identifier }}</dd>
                            
                            <dt class="col-sm-5">Common Name:</dt>
                            <dd class="col-sm-7">{{ $certificate->common_name }}</dd>
                            
                            <dt class="col-sm-5">Environment:</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-{{ $certificate->environment_type === 'Production' ? 'danger' : 'warning' }}">
                                    {{ $certificate->environment_type }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Status:</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-{{ $certificate->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($certificate->status) }}
                                </span>
                            </dd>
                            
                            <dt class="col-sm-5">Country:</dt>
                            <dd class="col-sm-7">{{ $certificate->country_name }}</dd>
                            
                            <dt class="col-sm-5">Business Category:</dt>
                            <dd class="col-sm-7">{{ $certificate->business_category ?? 'N/A' }}</dd>
                            
                            <dt class="col-sm-5">Created:</dt>
                            <dd class="col-sm-7">{{ $certificate->created_at->format('M d, Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onboarding Process -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Onboarding Process</h5>
            </div>
            <div class="card-body">
                <!-- Step 1: Generate CSR -->
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        @if($certificate->csr)
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        @else
                            <i class="fas fa-circle text-muted fa-lg"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Step 1: Generate CSR (Certificate Signing Request)</h6>
                        <p class="mb-1 text-muted small">
                            Generate the private key and certificate signing request.
                        </p>
                    </div>
                    <div>
                        @if(!$certificate->csr)
                            <button class="btn btn-primary btn-sm" onclick="generateCSR()">
                                <i class="fas fa-key me-1"></i> Generate CSR
                            </button>
                        @else
                            <span class="badge bg-success">Generated</span>
                        @endif
                    </div>
                </div>

                <!-- Step 2: Get Compliance CSID -->
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        @if($certificate->ccsid_binarySecurityToken)
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        @elseif($certificate->csr)
                            <i class="fas fa-circle-dot text-warning fa-lg"></i>
                        @else
                            <i class="fas fa-circle text-muted fa-lg"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Step 2: Get Compliance CSID</h6>
                        <p class="mb-1 text-muted small">
                            Obtain compliance certificate security identifier using OTP.
                        </p>
                    </div>
                    <div>
                        @if(!$certificate->ccsid_binarySecurityToken)
                            <button class="btn btn-primary btn-sm" onclick="getComplianceCSID()" 
                                    {{ !$certificate->csr ? 'disabled' : '' }}>
                                <i class="fas fa-certificate me-1"></i> Get Compliance CSID
                            </button>
                        @else
                            <span class="badge bg-success">Obtained</span>
                        @endif
                    </div>
                </div>

                <!-- Step 3: Get Production CSID -->
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        @if($certificate->pcsid_binarySecurityToken)
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        @elseif($certificate->ccsid_binarySecurityToken)
                            <i class="fas fa-circle-dot text-warning fa-lg"></i>
                        @else
                            <i class="fas fa-circle text-muted fa-lg"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Step 3: Get Production CSID</h6>
                        <p class="mb-1 text-muted small">
                            Obtain production certificate for live invoice processing.
                        </p>
                    </div>
                    <div>
                        @if(!$certificate->pcsid_binarySecurityToken)
                            <button class="btn btn-primary btn-sm" onclick="getProductionCSID()" 
                                    {{ !$certificate->ccsid_binarySecurityToken ? 'disabled' : '' }}>
                                <i class="fas fa-certificate me-1"></i> Get Production CSID
                            </button>
                        @else
                            <span class="badge bg-success">Obtained</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Status Overview -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Status Overview</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">CSR Generated</small>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-{{ $certificate->csr ? 'success' : 'secondary' }}" 
                             style="width: 100%"></div>
                    </div>
                </div>
                
                <div class="mb-2">
                    <small class="text-muted">Compliance CSID</small>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-{{ $certificate->ccsid_binarySecurityToken ? 'success' : 'secondary' }}" 
                             style="width: 100%"></div>
                    </div>
                </div>
                
                <div class="mb-2">
                    <small class="text-muted">Production CSID</small>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-{{ $certificate->pcsid_binarySecurityToken ? 'success' : 'secondary' }}" 
                             style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('zatca.onboarding.edit', $certificate) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-2"></i>Edit Certificate
                </a>
                
                <a href="{{ route('zatca.invoices.create') }}" class="btn btn-success w-100 mb-2"
                   {{ !$certificate->ccsid_binarySecurityToken ? 'disabled' : '' }}>
                    <i class="fas fa-file-invoice me-2"></i>Create Invoice
                </a>
                
                <a href="{{ route('zatca.onboarding.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left me-2"></i>Back to Certificates
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0" id="loadingMessage">Processing...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function generateCSR() {
    showLoading('Generating CSR...');
    
    $.post('{{ route("zatca.onboarding.generate-csr", $certificate) }}')
        .done(function(response) {
            hideLoading();
            if (response.success) {
                showAlert('success', response.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', response.message);
            }
        })
        .fail(function(xhr) {
            hideLoading();
            showAlert('error', 'Failed to generate CSR');
        });
}

function getComplianceCSID() {
    showLoading('Getting Compliance CSID...');
    
    $.post('{{ route("zatca.onboarding.compliance-csid", $certificate) }}')
        .done(function(response) {
            hideLoading();
            if (response.success) {
                showAlert('success', response.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', response.message);
            }
        })
        .fail(function(xhr) {
            hideLoading();
            showAlert('error', 'Failed to get Compliance CSID');
        });
}

function getProductionCSID() {
    showLoading('Getting Production CSID...');
    
    $.post('{{ route("zatca.onboarding.production-csid", $certificate) }}')
        .done(function(response) {
            hideLoading();
            if (response.success) {
                showAlert('success', response.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', response.message);
            }
        })
        .fail(function(xhr) {
            hideLoading();
            showAlert('error', 'Failed to get Production CSID');
        });
}

function showLoading(message) {
    $('#loadingMessage').text(message);
    $('#loadingModal').modal('show');
}

function hideLoading() {
    $('#loadingModal').modal('hide');
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.card').first().before(alert);
}
</script>
@endsection