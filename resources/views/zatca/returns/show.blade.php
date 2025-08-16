@extends('layouts.app')

@section('title', 'Return Invoice Details')
@section('page-title', 'Return Invoice: ' . $returnInvoice->invoice_number)

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Return Invoice Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Return Invoice Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Return Number:</dt>
                            <dd class="col-sm-7">{{ $returnInvoice->invoice_number }}</dd>
                            
                            <dt class="col-sm-5">UUID:</dt>
                            <dd class="col-sm-7"><small>{{ $returnInvoice->uuid }}</small></dd>
                            
                            <dt class="col-sm-5">Type:</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-warning">Credit Note (381)</span>
                                <span class="badge bg-{{ $returnInvoice->invoice_subtype === '01' ? 'primary' : 'secondary' }}">
                                    {{ $returnInvoice->invoice_subtype === '01' ? 'Standard' : 'Simplified' }}
                                </span>
                            </dd>
                            
                            <dt class="col-sm-5">Return Date:</dt>
                            <dd class="col-sm-7">{{ $returnInvoice->issue_date->format('M d, Y') }} at {{ $returnInvoice->issue_time }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Return Reason:</dt>
                            <dd class="col-sm-7">{{ $returnInvoice->return_reason ?? 'N/A' }}</dd>
                            
                            <dt class="col-sm-5">Return Type:</dt>
                            <dd class="col-sm-7">
                                @if($returnInvoice->return_type)
                                    <span class="badge bg-{{ $returnInvoice->return_type === 'full' ? 'warning' : 'info' }}">
                                        {{ ucfirst($returnInvoice->return_type) }} Return
                                    </span>
                                @endif
                            </dd>
                            
                            <dt class="col-sm-5">Original Invoice:</dt>
                            <dd class="col-sm-7">
                                @if($originalInvoice)
                                    <a href="{{ route('zatca.invoices.show', $originalInvoice) }}" class="btn btn-sm btn-outline-primary">
                                        {{ $returnInvoice->original_invoice_number }}
                                    </a>
                                @else
                                    <span class="badge bg-secondary">{{ $returnInvoice->original_invoice_number }}</span>
                                @endif
                            </dd>
                            
                            <dt class="col-sm-5">Certificate:</dt>
                            <dd class="col-sm-7">{{ $returnInvoice->certificate->organization_name }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        @if($returnInvoice->buyer_info)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Customer Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Name:</dt>
                            <dd class="col-sm-8">{{ $returnInvoice->buyer_info['name'] ?? 'N/A' }}</dd>
                            
                            <dt class="col-sm-4">VAT Number:</dt>
                            <dd class="col-sm-8">{{ $returnInvoice->buyer_info['vat_number'] ?? 'N/A' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Address:</dt>
                            <dd class="col-sm-8">{{ $returnInvoice->buyer_info['address'] ?? 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Return Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Returned Items</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Tax Rate</th>
                                <th>Tax Amount</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($returnInvoice->line_items)
                                @foreach($returnInvoice->line_items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-danger">{{ number_format(abs($item['quantity'] ?? 0), 2) }}</td>
                                        <td>{{ number_format($item['unit_price'] ?? 0, 2) }} {{ $returnInvoice->currency }}</td>
                                        <td>{{ number_format($item['tax_rate'] ?? 0, 1) }}%</td>
                                        <td class="text-danger">{{ number_format(abs($item['tax_amount'] ?? 0), 2) }} {{ $returnInvoice->currency }}</td>
                                        <td class="text-danger">{{ number_format(abs($item['total_amount'] ?? 0), 2) }} {{ $returnInvoice->currency }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6">Subtotal:</th>
                                <th class="text-danger">{{ number_format(abs($returnInvoice->subtotal), 2) }} {{ $returnInvoice->currency }}</th>
                            </tr>
                            <tr>
                                <th colspan="6">Tax Amount:</th>
                                <th class="text-danger">{{ number_format(abs($returnInvoice->tax_amount), 2) }} {{ $returnInvoice->currency }}</th>
                            </tr>
                            <tr class="table-warning">
                                <th colspan="6">Total Return Amount:</th>
                                <th class="text-danger">{{ number_format(abs($returnInvoice->total_amount), 2) }} {{ $returnInvoice->currency }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Processing Steps -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Processing Steps</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Step 1: Generate XML -->
                    <div class="col-md-3 mb-3">
                        <div class="d-flex align-items-center">
                            @if($returnInvoice->invoice_xml)
                                <span class="badge bg-success me-2">1</span>
                            @else
                                <span class="badge bg-secondary me-2">1</span>
                            @endif
                            <div>
                                <h6 class="mb-1">Step 1: Generate XML</h6>
                                @if(!$returnInvoice->invoice_xml)
                                    <button class="btn btn-primary btn-sm" onclick="generateReturnXML()">
                                        <i class="fas fa-code me-1"></i> Generate XML
                                    </button>
                                @else
                                    <span class="badge bg-success">Generated</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Sign Invoice -->
                    <div class="col-md-3 mb-3">
                        <div class="d-flex align-items-center">
                            @if($returnInvoice->signed_xml)
                                <span class="badge bg-success me-2">2</span>
                            @else
                                <span class="badge bg-secondary me-2">2</span>
                            @endif
                            <div>
                                <h6 class="mb-1">Step 2: Sign Return</h6>
                                @if($returnInvoice->invoice_xml && !$returnInvoice->signed_xml)
                                    <button class="btn btn-primary btn-sm" onclick="signReturn()">
                                        <i class="fas fa-signature me-1"></i> Sign
                                    </button>
                                @elseif($returnInvoice->signed_xml)
                                    <span class="badge bg-success">Signed</span>
                                @else
                                    <span class="text-muted">Requires XML</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Submit to ZATCA -->
                    <div class="col-md-3 mb-3">
                        <div class="d-flex align-items-center">
                            @if($returnInvoice->zatca_status === 'reported' || $returnInvoice->zatca_status === 'cleared')
                                <span class="badge bg-success me-2">3</span>
                            @else
                                <span class="badge bg-secondary me-2">3</span>
                            @endif
                            <div>
                                <h6 class="mb-1">Step 3: Submit to ZATCA</h6>
                                @if($returnInvoice->signed_xml && $returnInvoice->zatca_status === 'pending')
                                    <button class="btn btn-primary btn-sm" onclick="submitReturn()">
                                        <i class="fas fa-paper-plane me-1"></i> Submit
                                    </button>
                                @elseif($returnInvoice->zatca_status === 'reported')
                                    <span class="badge bg-success">Reported</span>
                                @elseif($returnInvoice->zatca_status === 'cleared')
                                    <span class="badge bg-success">Cleared</span>
                                @else
                                    <span class="text-muted">Requires Signing</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Generate QR -->
                    <div class="col-md-3 mb-3">
                        <div class="d-flex align-items-center">
                            @if($returnInvoice->qr_code)
                                <span class="badge bg-success me-2">4</span>
                            @else
                                <span class="badge bg-secondary me-2">4</span>
                            @endif
                            <div>
                                <h6 class="mb-1">Step 4: Generate QR</h6>
                                @if($returnInvoice->signed_xml && !$returnInvoice->qr_code)
                                    <button class="btn btn-primary btn-sm" onclick="generateReturnQR()">
                                        <i class="fas fa-qrcode me-1"></i> Generate QR
                                    </button>
                                @elseif($returnInvoice->qr_code)
                                    <span class="badge bg-success">Generated</span>
                                @else
                                    <span class="text-muted">Requires Signing</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Display -->
        @if($returnInvoice->qr_code)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">QR Code</h6>
                </div>
                <div class="card-body text-center">
                    <div class="bg-light p-3 rounded">
                        <img src="data:image/png;base64,{{ app('DNS2D')->getBarcodePNG($returnInvoice->qr_code, 'QRCODE', 4, 4) }}" 
                             alt="Return QR Code" 
                             style="width: 150px; height: 150px;">
                    </div>
                    <p class="text-muted mt-2 mb-0"><small>Scan to verify return authenticity</small></p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- ZATCA Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">ZATCA Status</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <span class="badge bg-{{ $returnInvoice->getStatusBadgeColor() }} p-2 fs-6">
                        {{ ucfirst($returnInvoice->zatca_status) }}
                    </span>
                    
                    @if($returnInvoice->zatca_uuid)
                        <div class="mt-3">
                            <small class="text-muted">ZATCA UUID</small><br>
                            <small>{{ $returnInvoice->zatca_uuid }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('zatca.returns.index') }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Returns
                </a>
                
                <a href="{{ route('zatca.invoices.print', $returnInvoice) }}" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                    <i class="fas fa-print me-2"></i>Print Return Invoice
                </a>
                
                @if($originalInvoice)
                    <a href="{{ route('zatca.invoices.show', $originalInvoice) }}" class="btn btn-outline-info w-100 mb-2">
                        <i class="fas fa-eye me-2"></i>View Original Invoice
                    </a>
                @endif

                @if($returnInvoice->isPending())
                    <form action="{{ route('zatca.returns.destroy', $returnInvoice) }}" 
                          method="POST" class="d-inline w-100"
                          onsubmit="return confirm('Are you sure you want to delete this return?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Delete Return
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function generateReturnXML() {
    showLoading('Generating XML...');
    $.post('{{ route("zatca.returns.generate-xml", $returnInvoice) }}')
        .done(function(response) {
            if (response.success) {
                showAlert('success', 'XML generated successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', response.message || 'Failed to generate XML');
            }
        })
        .fail(function() {
            showAlert('error', 'Failed to generate XML');
        })
        .always(function() {
            hideLoading();
        });
}

function signReturn() {
    showLoading('Signing return...');
    $.post('{{ route("zatca.returns.process", $returnInvoice) }}', {
        action: 'sign',
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            showAlert('success', 'Return signed successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', response.message || 'Failed to sign return');
        }
    })
    .fail(function() {
        showAlert('error', 'Failed to sign return');
    })
    .always(function() {
        hideLoading();
    });
}

function submitReturn() {
    showLoading('Submitting to ZATCA...');
    $.post('{{ route("zatca.returns.process", $returnInvoice) }}', {
        action: 'submit',
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            showAlert('success', 'Return submitted successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', response.message || 'Failed to submit return');
        }
    })
    .fail(function() {
        showAlert('error', 'Failed to submit return');
    })
    .always(function() {
        hideLoading();
    });
}

function generateReturnQR() {
    showLoading('Generating QR Code...');
    $.post('{{ route("zatca.returns.process", $returnInvoice) }}', {
        action: 'generate_qr',
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            showAlert('success', 'QR code generated successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', response.message || 'Failed to generate QR code');
        }
    })
    .fail(function() {
        showAlert('error', 'Failed to generate QR code');
    })
    .always(function() {
        hideLoading();
    });
}

function showLoading(message) {
    // Add loading modal logic here
}

function hideLoading() {
    // Hide loading modal logic here
}

function showAlert(type, message) {
    // Add alert logic here
    alert(message);
}
</script>
@endsection