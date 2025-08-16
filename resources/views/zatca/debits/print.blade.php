<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debit Note - {{ $debitNote->invoice_number }}</title>
    
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            body { font-size: 12px; }
        }
        
        .invoice-header {
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .invoice-title {
            color: #28a745;
            font-weight: bold;
            font-size: 2rem;
        }
        
        .company-info {
            text-align: right;
        }
        
        .invoice-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .table th {
            background-color: #28a745;
            color: white;
        }
        
        .total-section {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            border: 2px solid #28a745;
        }
        
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
        
        .additional-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Print Button -->
        <div class="row no-print mb-3">
            <div class="col-12">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Debit Note
                </button>
                <a href="{{ route('zatca.debits.show', $debitNote) }}" class="btn btn-secondary ms-2">
                    <i class="fas fa-arrow-left"></i> Back to Details
                </a>
            </div>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="row">
                <div class="col-8">
                    <h1 class="invoice-title">DEBIT NOTE</h1>
                    <p class="text-muted mb-1">Additional Charges / Price Adjustment</p>
                    <p class="mb-0"><strong>Debit Note #:</strong> {{ $debitNote->invoice_number }}</p>
                </div>
                <div class="col-4 company-info">
                    <h4>{{ $debitNote->seller_info['name'] }}</h4>
                    <p class="mb-1">{{ $debitNote->seller_info['address'] }}</p>
                    <p class="mb-1"><strong>VAT:</strong> {{ $debitNote->seller_info['vat_number'] }}</p>
                    <p class="mb-0"><strong>Date:</strong> {{ $debitNote->issue_date->format('Y-m-d') }}</p>
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Debit Note Information</strong></h6>
                    <p class="mb-1"><strong>UUID:</strong> <small>{{ $debitNote->uuid }}</small></p>
                    <p class="mb-1"><strong>Issue Time:</strong> {{ $debitNote->issue_time }}</p>
                    <p class="mb-1"><strong>Type:</strong> 
                        <span class="badge bg-success">Debit Note (383)</span>
                        <span class="badge bg-info ms-1">{{ $debitNote->invoice_subtype === '01' ? 'Standard' : 'Simplified' }}</span>
                    </p>
                    <p class="mb-0"><strong>ICV:</strong> {{ $debitNote->icv }}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Original Invoice Reference</strong></h6>
                    <p class="mb-1"><strong>Original Invoice:</strong> {{ $debitNote->original_invoice_number }}</p>
                    <p class="mb-1"><strong>Reason:</strong> {{ $debitNote->debit_reason }}</p>
                    <p class="mb-1"><strong>Type:</strong> 
                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $debitNote->debit_type ?? 'N/A')) }}</span>
                    </p>
                    <p class="mb-0"><strong>Currency:</strong> {{ $debitNote->currency }}</p>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        @if($debitNote->buyer_info)
        <div class="row mb-4">
            <div class="col-12">
                <h6><strong>Bill To:</strong></h6>
                <div class="border p-3 rounded">
                    <h6>{{ $debitNote->buyer_info['name'] }}</h6>
                    <p class="mb-1">{{ $debitNote->buyer_info['address'] ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>VAT Number:</strong> {{ $debitNote->buyer_info['vat_number'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Line Items -->
        <div class="row mb-4">
            <div class="col-12">
                <h6><strong>Additional Charges/Items:</strong></h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Line Total</th>
                            <th class="text-center">Tax %</th>
                            <th class="text-end">Tax Amount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($debitNote->line_items as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-center">{{ number_format($item['quantity'], 2) }}</td>
                            <td class="text-end">{{ number_format($item['unit_price'], 2) }}</td>
                            <td class="text-end">{{ number_format($item['line_total'], 2) }}</td>
                            <td class="text-center">{{ $item['tax_rate'] }}%</td>
                            <td class="text-end">{{ number_format($item['tax_amount'], 2) }}</td>
                            <td class="text-end"><strong>{{ number_format($item['total_with_tax'] ?? $item['total_amount'], 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals -->
        <div class="row">
            <div class="col-md-8">
                @if($debitNote->qr_code)
                <div class="qr-code">
                    <h6><strong>QR Code</strong></h6>
                    <img src="data:image/png;base64,{{ app('DNS2D')->getBarcodePNG($debitNote->qr_code, 'QRCODE', 4, 4) }}" 
                         alt="QR Code" style="width: 120px; height: 120px;">
                    <p class="small text-muted mt-2">Scan for verification</p>
                </div>
                @endif
            </div>
            <div class="col-md-4">
                <div class="total-section">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td><strong>Subtotal:</strong></td>
                            <td class="text-end">{{ number_format($debitNote->subtotal, 2) }} {{ $debitNote->currency }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tax Amount:</strong></td>
                            <td class="text-end">{{ number_format($debitNote->tax_amount, 2) }} {{ $debitNote->currency }}</td>
                        </tr>
                        <tr class="border-top">
                            <td><h5><strong>Total Additional Amount:</strong></h5></td>
                            <td class="text-end"><h5><strong>{{ number_format($debitNote->total_amount, 2) }} {{ $debitNote->currency }}</strong></h5></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="additional-info">
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Important Note:</strong></h6>
                    <p class="small">This debit note represents additional charges to be added to the original invoice amount. 
                    The total amount due is now the original invoice amount plus this debit note amount.</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>ZATCA Status:</strong></h6>
                    <p class="small">
                        <span class="badge bg-{{ $debitNote->getStatusBadgeColor() }}">{{ ucfirst($debitNote->zatca_status) }}</span>
                        @if($debitNote->submitted_at)
                            <br><small class="text-muted">Submitted: {{ $debitNote->submitted_at->format('Y-m-d H:i:s') }}</small>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="row mt-4 pt-3 border-top">
            <div class="col-12 text-center">
                <p class="small text-muted mb-0">
                    This is a computer-generated debit note. For any queries, please contact us.
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>