@extends('layouts.app')

@section('title', 'Invoice Details')
@section('page-title', 'Invoice: ' . $invoice->invoice_number)

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Invoice Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Invoice Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Invoice Number:</dt>
                            <dd class="col-sm-7">{{ $invoice->invoice_number }}</dd>
                            
                            <dt class="col-sm-5">UUID:</dt>
                            <dd class="col-sm-7"><small>{{ $invoice->uuid }}</small></dd>
                            
                            <dt class="col-sm-5">Type:</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-info">{{ $invoice->getInvoiceTypeName() }}</span>
                                <span class="badge bg-{{ $invoice->invoice_subtype === '01' ? 'primary' : 'secondary' }}">
                                    {{ $invoice->invoice_subtype === '01' ? 'Standard' : 'Simplified' }}
                                </span>
                            </dd>
                            
                            <dt class="col-sm-5">Issue Date:</dt>
                            <dd class="col-sm-7">{{ $invoice->issue_date->format('M d, Y') }} at {{ $invoice->issue_time }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Due Date:</dt>
                            <dd class="col-sm-7">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</dd>
                            
                            <dt class="col-sm-5">ICV:</dt>
                            <dd class="col-sm-7">{{ $invoice->icv }}</dd>
                            
                            <dt class="col-sm-5">Certificate:</dt>
                            <dd class="col-sm-7">{{ $invoice->certificate->organization_name }}</dd>
                            
                            <dt class="col-sm-5">Created:</dt>
                            <dd class="col-sm-7">{{ $invoice->created_at->format('M d, Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parties Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Parties Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Seller</h6>
                        <strong>{{ $invoice->seller_info['name'] }}</strong><br>
                        VAT: {{ $invoice->seller_info['vat_number'] }}<br>
                        {{ $invoice->seller_info['address'] }}
                    </div>
                    <div class="col-md-6">
                        <h6>Buyer</h6>
                        @if($invoice->buyer_info)
                            <strong>{{ $invoice->buyer_info['name'] }}</strong><br>
                            @if(isset($invoice->buyer_info['vat_number']) && $invoice->buyer_info['vat_number'])
                                VAT: {{ $invoice->buyer_info['vat_number'] }}<br>
                            @endif
                            {{ $invoice->buyer_info['address'] ?? '' }}
                        @else
                            <span class="text-muted">No buyer information</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Line Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->line_items as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td class="text-end">{{ number_format($item['quantity'], 2) }}</td>
                                    <td class="text-end">{{ number_format($item['unit_price'], 2) }}</td>
                                    <td class="text-end">{{ number_format($item['line_total'], 2) }}</td>
                                    <td class="text-end">
                                        {{ number_format($item['tax_amount'], 2) }}
                                        <small class="text-muted">({{ $item['tax_rate'] }}%)</small>
                                    </td>
                                    <td class="text-end">{{ number_format($item['total_with_tax'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <th colspan="3">Subtotal:</th>
                                <th class="text-end">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</th>
                                <th>Tax Total:</th>
                                <th class="text-end">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</th>
                            </tr>
                            <tr class="table-primary">
                                <th colspan="4">Total Amount:</th>
                                <th colspan="2" class="text-end">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ZATCA Processing -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">ZATCA Processing</h5>
            </div>
            <div class="card-body">
                <!-- Step 1: Generate XML -->
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        @if($invoice->invoice_xml)
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        @else
                            <i class="fas fa-circle text-muted fa-lg"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Step 1: Generate XML</h6>
                        <p class="mb-1 text-muted small">Generate ZATCA-compliant XML structure.</p>
                    </div>
                    <div>
                        @if(!$invoice->invoice_xml)
                            <button class="btn btn-primary btn-sm" onclick="generateXML()">
                                <i class="fas fa-code me-1"></i> Generate XML
                            </button>
                        @else
                            <span class="badge bg-success">Generated</span>
                        @endif
                    </div>
                </div>

                <!-- Step 2: Sign Invoice -->
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        @if($invoice->signed_xml)
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        @elseif($invoice->invoice_xml)
                            <i class="fas fa-circle-dot text-warning fa-lg"></i>
                        @else
                            <i class="fas fa-circle text-muted fa-lg"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Step 2: Sign Invoice</h6>
                        <p class="mb-1 text-muted small">Digitally sign the invoice using certificate.</p>
                    </div>
                    <div>
                        @if(!$invoice->signed_xml)
                            <button class="btn btn-primary btn-sm" onclick="signInvoice()" 
                                    {{ !$invoice->invoice_xml ? 'disabled' : '' }}>
                                <i class="fas fa-signature me-1"></i> Sign Invoice
                            </button>
                        @else
                            <span class="badge bg-success">Signed</span>
                        @endif
                    </div>
                </div>

                <!-- Step 3: Submit to ZATCA -->
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        @if($invoice->isCleared() || $invoice->isReported())
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        @elseif($invoice->signed_xml)
                            <i class="fas fa-circle-dot text-warning fa-lg"></i>
                        @else
                            <i class="fas fa-circle text-muted fa-lg"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Step 3: Submit to ZATCA</h6>
                        <p class="mb-1 text-muted small">
                            {{ $invoice->isStandardInvoice() ? 'Clear invoice with ZATCA' : 'Report invoice to ZATCA' }}.
                        </p>
                    </div>
                    <div>
                        @if(!$invoice->isCleared() && !$invoice->isReported())
                            <button class="btn btn-primary btn-sm" onclick="submitToZatca()" 
                                    {{ !$invoice->signed_xml ? 'disabled' : '' }}>
                                <i class="fas fa-paper-plane me-1"></i> Submit to ZATCA
                            </button>
                        @else
                            <span class="badge bg-success">{{ ucfirst($invoice->zatca_status) }}</span>
                        @endif
                    </div>
                </div>

                <!-- Step 4: Generate QR Code -->
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        @if($invoice->qr_code)
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        @elseif($invoice->signed_xml)
                            <i class="fas fa-circle-dot text-warning fa-lg"></i>
                        @else
                            <i class="fas fa-circle text-muted fa-lg"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Step 4: Generate QR Code</h6>
                        <p class="mb-1 text-muted small">Generate QR code for invoice verification.</p>
                    </div>
                    <div>
                        @if(!$invoice->qr_code)
                            <button class="btn btn-primary btn-sm" onclick="generateQRCode()">
                                <i class="fas fa-qrcode me-1"></i> Generate QR
                            </button>
                        @else
                            <span class="badge bg-success">Generated</span>
                        @endif
                    </div>
                </div>

                @if($invoice->error_message)
                    <div class="alert alert-danger">
                        <strong>Error:</strong> {{ $invoice->error_message }}
                    </div>
                @endif
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
                    <small class="text-muted">ZATCA Status</small><br>
                    <span class="badge bg-{{ $invoice->getStatusBadgeColor() }}">
                        {{ ucfirst($invoice->zatca_status) }}
                    </span>
                </div>
                
                @if($invoice->submitted_at)
                    <div class="mb-2">
                        <small class="text-muted">Submitted At</small><br>
                        {{ $invoice->submitted_at->format('M d, Y H:i') }}
                    </div>
                @endif

                @if($invoice->cleared_at)
                    <div class="mb-2">
                        <small class="text-muted">Cleared At</small><br>
                        {{ $invoice->cleared_at->format('M d, Y H:i') }}
                    </div>
                @endif

                @if($invoice->zatca_uuid)
                    <div class="mb-2">
                        <small class="text-muted">ZATCA UUID</small><br>
                        <small>{{ $invoice->zatca_uuid }}</small>
                    </div>
                @endif
            </div>
        </div>

        @if($invoice->qr_code)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">QR Code</h6>
                </div>
                <div class="card-body text-center">
                    <div class="bg-light p-3 rounded">
                        <img src="data:image/png;base64,{{ app('DNS2D')->getBarcodePNG($invoice->qr_code, 'QRCODE', 4, 4) }}" 
                             alt="QR Code" 
                             style="width: 150px; height: 150px;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="display: none;">
                            <i class="fas fa-qrcode fa-3x text-muted"></i><br>
                            <small class="text-muted">QR Code Error</small>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Base64: {{ Str::limit($invoice->qr_code, 20) }}...</small>
                    <p class="text-muted mt-2 mb-0"><small>Scan to verify invoice authenticity</small></p>
                </div>
            </div>
        @endif

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('zatca.invoices.index') }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Invoices
                </a>
                
                <a href="{{ route('zatca.invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                    <i class="fas fa-print me-2"></i>Print Invoice
                </a>

                @if($invoice->zatca_status === 'reported' || $invoice->zatca_status === 'cleared')
                    <a href="{{ route('zatca.returns.create-from', $invoice) }}" class="btn btn-outline-warning w-100 mb-2">
                        <i class="fas fa-undo me-2"></i>Create Return
                    </a>
                @endif
                
                @if($invoice->isPending())
                    <form action="{{ route('zatca.invoices.destroy', $invoice) }}" 
                          method="POST" class="d-inline w-100"
                          onsubmit="return confirm('Are you sure you want to delete this invoice?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Delete Invoice
                        </button>
                    </form>
                @endif
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
function generateXML() {
    showLoading('Generating XML...');
    
    $.post('{{ route("zatca.invoices.generate-xml", $invoice) }}')
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
            showAlert('error', 'Failed to generate XML');
        });
}

function signInvoice() {
    showLoading('Signing invoice...');
    
    $.post('{{ route("zatca.invoices.sign", $invoice) }}')
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
            showAlert('error', 'Failed to sign invoice');
        });
}

function submitToZatca() {
    showLoading('Submitting to ZATCA...');
    
    $.post('{{ route("zatca.invoices.submit", $invoice) }}')
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
            showAlert('error', 'Failed to submit to ZATCA');
        });
}

function generateQRCode() {
    showLoading('Generating QR Code...');
    
    $.post('{{ route("zatca.invoices.qr-code", $invoice) }}')
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
            showAlert('error', 'Failed to generate QR code');
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