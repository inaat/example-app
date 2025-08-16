@extends('layouts.app')

@section('title', 'Debit Note Details')
@section('page-title', 'Debit Note: ' . $debitNote->invoice_number)

@section('page-actions')
    <div class="btn-group" role="group">
        @if($debitNote->isPending())
            <button type="button" class="btn btn-primary" onclick="generateXML()">
                <i class="fas fa-code me-2"></i>Generate XML
            </button>
        @endif

        @if($debitNote->invoice_xml && !$debitNote->signed_xml)
            <button type="button" class="btn btn-warning" onclick="signInvoice()">
                <i class="fas fa-signature me-2"></i>Sign Invoice
            </button>
        @endif

        @if($debitNote->signed_xml && !$debitNote->qr_code)
            <button type="button" class="btn btn-info" onclick="generateQR()">
                <i class="fas fa-qrcode me-2"></i>Generate QR
            </button>
        @endif

        @if($debitNote->signed_xml && $debitNote->isPending())
            <button type="button" class="btn btn-success" onclick="submitToZatca()">
                <i class="fas fa-paper-plane me-2"></i>Submit to ZATCA
            </button>
        @endif

        <a href="{{ route('zatca.debits.print', $debitNote) }}" class="btn btn-outline-info" target="_blank">
            <i class="fas fa-print me-2"></i>Print
        </a>
        
        <a href="{{ route('zatca.debits.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Debit Note Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Debit Note Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 40%;">Debit Note Number:</th>
                                <td><strong>{{ $debitNote->invoice_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>UUID:</th>
                                <td><code>{{ $debitNote->uuid }}</code></td>
                            </tr>
                            <tr>
                                <th>Issue Date:</th>
                                <td>{{ $debitNote->issue_date->format('Y-m-d') }} {{ $debitNote->issue_time }}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>
                                    <span class="badge bg-success">Debit Note (383)</span>
                                    <span class="badge bg-info ms-1">
                                        {{ $debitNote->invoice_subtype === '01' ? 'Standard' : 'Simplified' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Original Invoice:</th>
                                <td>
                                    <strong>{{ $debitNote->original_invoice_number }}</strong>
                                    @if($originalInvoice)
                                        <br><a href="{{ route('zatca.invoices.show', $originalInvoice) }}" class="btn btn-sm btn-outline-primary mt-1">
                                            <i class="fas fa-eye"></i> View Original
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 40%;">Reason:</th>
                                <td>{{ $debitNote->debit_reason }}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst(str_replace('_', ' ', $debitNote->debit_type ?? 'N/A')) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Currency:</th>
                                <td>{{ $debitNote->currency }}</td>
                            </tr>
                            <tr>
                                <th>ICV:</th>
                                <td>{{ $debitNote->icv }}</td>
                            </tr>
                            <tr>
                                <th>Certificate:</th>
                                <td>{{ $debitNote->certificate->organization_name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        @if($debitNote->buyer_info)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>{{ $debitNote->buyer_info['name'] }}</strong><br>
                        <small class="text-muted">{{ $debitNote->buyer_info['address'] ?? 'N/A' }}</small>
                    </div>
                    <div class="col-md-6">
                        <strong>VAT Number:</strong> {{ $debitNote->buyer_info['vat_number'] ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Line Items -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Additional Charges/Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item/Service</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Line Total</th>
                                <th>Tax Rate</th>
                                <th>Tax Amount</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($debitNote->line_items as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ number_format($item['quantity'], 2) }}</td>
                                    <td>{{ number_format($item['unit_price'], 2) }} {{ $debitNote->currency }}</td>
                                    <td>{{ number_format($item['line_total'], 2) }} {{ $debitNote->currency }}</td>
                                    <td>{{ $item['tax_rate'] }}%</td>
                                    <td>{{ number_format($item['tax_amount'], 2) }} {{ $debitNote->currency }}</td>
                                    <td><strong>{{ number_format($item['total_with_tax'] ?? $item['total_amount'], 2) }} {{ $debitNote->currency }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6">Subtotal:</th>
                                <th>{{ number_format($debitNote->subtotal, 2) }} {{ $debitNote->currency }}</th>
                            </tr>
                            <tr>
                                <th colspan="6">Tax Amount:</th>
                                <th>{{ number_format($debitNote->tax_amount, 2) }} {{ $debitNote->currency }}</th>
                            </tr>
                            <tr class="table-success">
                                <th colspan="6">Total Additional Amount:</th>
                                <th>{{ number_format($debitNote->total_amount, 2) }} {{ $debitNote->currency }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Status and Progress -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">ZATCA Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-{{ $debitNote->getStatusBadgeColor() }} me-2">
                        {{ ucfirst($debitNote->zatca_status) }}
                    </span>
                    @if($debitNote->submitted_at)
                        <small class="text-muted">{{ $debitNote->submitted_at->format('M d, Y H:i') }}</small>
                    @endif
                </div>

                <!-- Progress Steps -->
                <div class="progress-steps">
                    <div class="step {{ $debitNote->invoice_xml ? 'completed' : 'pending' }}">
                        <i class="fas fa-code"></i> XML Generated
                    </div>
                    <div class="step {{ $debitNote->signed_xml ? 'completed' : 'pending' }}">
                        <i class="fas fa-signature"></i> Invoice Signed
                    </div>
                    <div class="step {{ $debitNote->qr_code ? 'completed' : 'pending' }}">
                        <i class="fas fa-qrcode"></i> QR Code Generated
                    </div>
                    <div class="step {{ $debitNote->zatca_status === 'reported' || $debitNote->zatca_status === 'cleared' ? 'completed' : 'pending' }}">
                        <i class="fas fa-check"></i> ZATCA Submitted
                    </div>
                </div>

                @if($debitNote->error_message)
                    <div class="alert alert-danger mt-3">
                        <strong>Error:</strong> {{ $debitNote->error_message }}
                    </div>
                @endif
            </div>
        </div>

        @if($debitNote->qr_code)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">QR Code</h5>
            </div>
            <div class="card-body text-center">
                <img src="data:image/png;base64,{{ app('DNS2D')->getBarcodePNG($debitNote->qr_code, 'QRCODE', 4, 4) }}" 
                     alt="QR Code" class="img-fluid" style="max-width: 200px;">
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 mb-0">Processing request...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.progress-steps {
    margin-top: 20px;
}

.step {
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    border-left: 4px solid #ddd;
}

.step.completed {
    background-color: #d4edda;
    border-left-color: #28a745;
    color: #155724;
}

.step.pending {
    background-color: #f8f9fa;
    border-left-color: #6c757d;
    color: #6c757d;
}

.step i {
    margin-right: 8px;
    width: 20px;
}
</style>
@endsection

@section('scripts')
<script>
function showProcessing() {
    $('#processingModal').modal('show');
}

function hideProcessing() {
    $('#processingModal').modal('hide');
}

function generateXML() {
    showProcessing();
    
    $.post('{{ route("zatca.debits.generate-xml", $debitNote) }}')
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        })
        .fail(function(xhr) {
            alert('Error generating XML: ' + xhr.responseText);
        })
        .always(function() {
            hideProcessing();
        });
}

function signInvoice() {
    showProcessing();
    
    $.post('{{ route("zatca.debits.process", $debitNote) }}', {
        action: 'sign'
    })
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        })
        .fail(function(xhr) {
            alert('Error signing invoice: ' + xhr.responseText);
        })
        .always(function() {
            hideProcessing();
        });
}

function generateQR() {
    showProcessing();
    
    $.post('{{ route("zatca.debits.process", $debitNote) }}', {
        action: 'generate_qr'
    })
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        })
        .fail(function(xhr) {
            alert('Error generating QR: ' + xhr.responseText);
        })
        .always(function() {
            hideProcessing();
        });
}

function submitToZatca() {
    if (!confirm('Are you sure you want to submit this debit note to ZATCA?')) {
        return;
    }
    
    showProcessing();
    
    $.post('{{ route("zatca.debits.process", $debitNote) }}', {
        action: 'submit'
    })
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        })
        .fail(function(xhr) {
            alert('Error submitting to ZATCA: ' + xhr.responseText);
        })
        .always(function() {
            hideProcessing();
        });
}
</script>
@endsection