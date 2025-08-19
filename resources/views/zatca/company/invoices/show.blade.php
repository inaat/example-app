@extends('layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Company Invoice: {{ $invoice->invoice_number }}</h4>
                    <div>
                        <span class="badge badge-{{ $invoice->getStatusBadgeColor() }} badge-lg">
                            {{ ucfirst($invoice->zatca_status ?? 'pending') }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row">
                        {{-- Invoice Details --}}
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Invoice Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Invoice Number:</strong></td>
                                            <td>{{ $invoice->invoice_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>UUID:</strong></td>
                                            <td><small>{{ $invoice->uuid }}</small></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Type:</strong></td>
                                            <td>
                                                {{ $invoice->isSimplified() ? 'Simplified' : 'Standard' }}
                                                @if($invoice->isCreditNote())
                                                    Credit Note
                                                @elseif($invoice->isDebitNote())
                                                    Debit Note
                                                @else
                                                    Invoice
                                                @endif
                                                ({{ $invoice->invoice_type }})
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Issue Date:</strong></td>
                                            <td>{{ $invoice->issue_date->format('Y-m-d') }} {{ $invoice->issue_time }}</td>
                                        </tr>
                                        @if($invoice->due_date)
                                        <tr>
                                            <td><strong>Due Date:</strong></td>
                                            <td>{{ $invoice->due_date->format('Y-m-d') }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>ICV:</strong></td>
                                            <td>{{ $invoice->icv }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Currency:</strong></td>
                                            <td>{{ $invoice->currency }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Company Details --}}
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Company Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Company:</strong></td>
                                            <td>{{ $invoice->company->organization_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>VAT Number:</strong></td>
                                            <td>{{ $invoice->company->vat_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>CRN:</strong></td>
                                            <td>{{ $invoice->company->crn }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Environment:</strong></td>
                                            <td>{{ $invoice->company->getPortalEnvironment() }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Address:</strong></td>
                                            <td>{{ $invoice->company->registered_address }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Buyer Information --}}
                    @if($invoice->buyer_info)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Buyer Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Name:</strong> {{ $invoice->buyer_info['name'] ?? 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>VAT Number:</strong> {{ $invoice->buyer_info['vat_number'] ?? 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Address:</strong> {{ $invoice->buyer_info['address'] ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Line Items --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Invoice Items</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Description</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Tax Rate</th>
                                            <th>Line Total</th>
                                            <th>Tax Amount</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->line_items as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item['name'] }}</td>
                                                <td>{{ number_format($item['quantity'], 2) }}</td>
                                                <td>{{ number_format($item['unit_price'], 2) }}</td>
                                                <td>{{ number_format($item['tax_rate'], 2) }}%</td>
                                                <td>{{ number_format($item['line_total'], 2) }}</td>
                                                <td>{{ number_format($item['tax_amount'], 2) }}</td>
                                                <td>{{ number_format($item['total_with_tax'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-secondary">
                                            <th colspan="5">Totals:</th>
                                            <th>{{ number_format($invoice->subtotal, 2) }}</th>
                                            <th>{{ number_format($invoice->tax_amount, 2) }}</th>
                                            <th>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="btn-group" role="group">
                                @if(!$invoice->invoice_xml)
                                    <button class="btn btn-primary" onclick="generateXML()">
                                        <i class="fas fa-code"></i> Generate XML
                                    </button>
                                @else
                                    <button class="btn btn-success" disabled>
                                        <i class="fas fa-check"></i> XML Generated
                                    </button>
                                @endif

                                @if($invoice->invoice_xml && !$invoice->signed_xml)
                                    <button class="btn btn-warning" onclick="signInvoice()">
                                        <i class="fas fa-signature"></i> Sign Invoice
                                    </button>
                                @elseif($invoice->signed_xml)
                                    <button class="btn btn-success" disabled>
                                        <i class="fas fa-check"></i> Signed
                                    </button>
                                @endif

                                @if($invoice->signed_xml && !$invoice->qr_code)
                                    <button class="btn btn-info" onclick="generateQRCode()">
                                        <i class="fas fa-qrcode"></i> Generate QR Code
                                    </button>
                                @elseif($invoice->qr_code)
                                    <button class="btn btn-success" disabled>
                                        <i class="fas fa-check"></i> QR Generated
                                    </button>
                                @endif

                                @if($invoice->signed_xml && !$invoice->isSubmitted())
                                    <button class="btn btn-danger" onclick="submitToZatca()">
                                        <i class="fas fa-paper-plane"></i> Submit to ZATCA
                                    </button>
                                @elseif($invoice->isSubmitted())
                                    <button class="btn btn-success" disabled>
                                        <i class="fas fa-check"></i> Submitted
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- QR Code Section --}}
                    @if($invoice->qr_code)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">QR Code</h6>
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

                    {{-- ZATCA Response --}}
                    @if($invoice->zatca_response)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">ZATCA Response</h6>
                        </div>
                        <div class="card-body">
                            <pre><code>{{ json_encode($invoice->zatca_response, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                    @endif

                    {{-- Error Message --}}
                    @if($invoice->error_message)
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">Error Details</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-danger">{{ $invoice->error_message }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('zatca.company.invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Invoices
                        </a>
                        
                        <div>
                            @if($invoice->zatca_status === 'reported' || $invoice->zatca_status === 'cleared')
                                <a href="{{ route('zatca.company.invoices.create-return', $invoice) }}" class="btn btn-warning">
                                    <i class="fas fa-undo"></i> Create Return
                                </a>
                                <a href="{{ route('zatca.company.invoices.create-debit', $invoice) }}" class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i> Create Debit Note
                                </a>
                            @endif
                            
                            @if(!$invoice->isSubmitted())
                                <form method="POST" action="{{ route('zatca.company.invoices.destroy', $invoice) }}" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i> Delete Invoice
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('zatca.company.invoices.print', $invoice) }}" class="btn btn-info" target="_blank">
                                <i class="fas fa-print"></i> Print
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateXML() {
    fetch('{{ route("zatca.company.invoices.generate-xml", $invoice) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function signInvoice() {
    fetch('{{ route("zatca.company.invoices.sign", $invoice) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function generateQRCode() {
    fetch('{{ route("zatca.company.invoices.qr-code", $invoice) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function submitToZatca() {
    if (!confirm('Are you sure you want to submit this invoice to ZATCA? This action cannot be undone.')) {
        return;
    }
    
    fetch('{{ route("zatca.company.invoices.submit", $invoice) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
@endsection