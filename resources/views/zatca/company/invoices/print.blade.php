<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} - Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .detail-section {
            width: 48%;
        }
        .detail-section h3 {
            font-size: 14px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 5px;
        }
        .detail-label {
            font-weight: bold;
            min-width: 100px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .items-table .number {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        .totals table {
            margin-left: auto;
            margin-right: 0;
        }
        .totals td {
            padding: 5px 10px;
            border: none;
        }
        .totals .total-label {
            font-weight: bold;
        }
        .totals .final-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .status-section {
            margin-top: 30px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .credit-note-border {
            border-left: 5px solid #dc3545;
            background-color: #fff5f5;
        }
        .debit-note-border {
            border-left: 5px solid #198754;
            background-color: #f0fff4;
        }
        .return-amount {
            color: #dc3545;
            font-weight: bold;
        }
        .additional-amount {
            color: #198754;
            font-weight: bold;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="{{ $invoice->invoice_type === '383' ? 'credit-note-border' : ($invoice->invoice_type === '381' ? 'debit-note-border' : '') }}">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px;">
            Print {{ $invoice->invoice_type === '383' ? 'Credit Note' : ($invoice->invoice_type === '381' ? 'Debit Note' : 'Invoice') }}
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; margin-left: 10px;">Close</button>
    </div>

    <div class="invoice-header">
        @if($invoice->invoice_type === '383')
            <div class="invoice-title" style="color: #dc3545;">CREDIT NOTE / إشعار دائن</div>
            <div style="color: #dc3545; font-weight: bold;">Return/Refund Invoice</div>
        @elseif($invoice->invoice_type === '381')
            <div class="invoice-title" style="color: #198754;">DEBIT NOTE / إشعار مدين</div>
            <div style="color: #198754; font-weight: bold;">Additional Charges</div>
        @else
            <div class="invoice-title">TAX INVOICE / فاتورة ضريبية</div>
        @endif
        <div>{{ $invoice->invoice_type === '383' ? 'Credit Note' : ($invoice->invoice_type === '381' ? 'Debit Note' : 'Invoice') }} Number: {{ $invoice->invoice_number }}</div>
        <div>UUID: {{ $invoice->uuid }}</div>
        @if($invoice->original_invoice_number)
            <div style="margin-top: 10px; padding: 5px; background-color: #f8f9fa; border: 1px solid #dee2e6;">
                <strong>Original Invoice Reference: {{ $invoice->original_invoice_number }}</strong>
            </div>
        @endif
    </div>

    <div class="invoice-details">
        <div class="detail-section">
            <h3>Seller Information / معلومات البائع</h3>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span>{{ $invoice->seller_info['name'] ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">VAT Number:</span>
                <span>{{ $invoice->seller_info['vat_number'] ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span>{{ $invoice->seller_info['address'] ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="detail-section">
            <h3>Invoice Details / تفاصيل الفاتورة</h3>
            <div class="detail-row">
                <span class="detail-label">Issue Date:</span>
                <span>{{ $invoice->issue_date->format('Y-m-d') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Issue Time:</span>
                <span>{{ $invoice->issue_time }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type:</span>
                <span>{{ $invoice->getInvoiceTypeName() }} ({{ $invoice->invoice_subtype === '01' ? 'Standard' : 'Simplified' }})</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ICV:</span>
                <span>{{ $invoice->icv }}</span>
            </div>
        </div>
    </div>

    @if($invoice->buyer_info)
    <div class="detail-section" style="margin-bottom: 20px;">
        <h3>Buyer Information / معلومات المشتري</h3>
        <div class="detail-row">
            <span class="detail-label">Name:</span>
            <span>{{ $invoice->buyer_info['name'] ?? 'N/A' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">VAT Number:</span>
            <span>{{ $invoice->buyer_info['vat_number'] ?? 'N/A' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Address:</span>
            <span>{{ $invoice->buyer_info['address'] ?? 'N/A' }}</span>
        </div>
    </div>
    @endif

    @if($invoice->return_reason && ($invoice->invoice_type === '383' || $invoice->invoice_type === '381'))
    <div class="detail-section" style="margin-bottom: 20px; padding: 10px; background-color: {{ $invoice->invoice_type === '383' ? '#fff3cd' : '#d1f7c4' }}; border: 1px solid {{ $invoice->invoice_type === '383' ? '#ffc107' : '#28a745' }};">
        <h3 style="color: {{ $invoice->invoice_type === '383' ? '#856404' : '#155724' }};">
            {{ $invoice->invoice_type === '383' ? 'Return Reason / سبب الإرجاع' : 'Additional Charge Reason / سبب الرسوم الإضافية' }}
        </h3>
        <div class="detail-row">
            <span class="detail-label">Reason:</span>
            <span style="font-weight: bold;">{{ $invoice->return_reason }}</span>
        </div>
    </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                @if($invoice->invoice_type === '383')
                    <th>Returned Item / الصنف المرتجع</th>
                    <th>Returned Qty / الكمية المرتجعة</th>
                @elseif($invoice->invoice_type === '381')
                    <th>Additional Charge / الرسوم الإضافية</th>
                    <th>Quantity / الكمية</th>
                @else
                    <th>Description / الوصف</th>
                    <th>Quantity / الكمية</th>
                @endif
                <th>Unit Price / سعر الوحدة</th>
                <th>Tax Rate / معدل الضريبة</th>
                <th>Total / المجموع</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->line_items && is_array($invoice->line_items))
                @foreach($invoice->line_items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['name'] ?? 'N/A' }}</td>
                    <td class="number">{{ number_format($item['quantity'] ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($item['unit_price'] ?? 0, 2) }} {{ $invoice->currency }}</td>
                    <td class="number">{{ number_format($item['tax_rate'] ?? 0, 1) }}%</td>
                    <td class="number">{{ number_format($item['total_with_tax'] ?? 0, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                        No items found for this invoice
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                @if($invoice->invoice_type === '383')
                    <td class="total-label">Return Subtotal / المجموع الفرعي للإرجاع:</td>
                @elseif($invoice->invoice_type === '381')
                    <td class="total-label">Additional Charges Subtotal / مجموع الرسوم الإضافية:</td>
                @else
                    <td class="total-label">Subtotal / المجموع الفرعي:</td>
                @endif
                <td class="number">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
            </tr>
            <tr>
                <td class="total-label">Tax Amount / مبلغ الضريبة:</td>
                <td class="number">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</td>
            </tr>
            <tr class="final-total">
                @if($invoice->invoice_type === '383')
                    <td class="total-label" style="color: #dc3545;">Total Return Amount / إجمالي مبلغ الإرجاع:</td>
                    <td class="number" style="color: #dc3545;">-{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                @elseif($invoice->invoice_type === '381')
                    <td class="total-label" style="color: #198754;">Total Additional Amount / إجمالي المبلغ الإضافي:</td>
                    <td class="number" style="color: #198754;">+{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                @else
                    <td class="total-label">Total Amount / المبلغ الإجمالي:</td>
                    <td class="number">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                @endif
            </tr>
        </table>
    </div>

    <div class="status-section">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="flex: 1;">
                @if($invoice->invoice_type === '383')
                    <h3 style="color: #dc3545;">ZATCA Credit Note Status / حالة إشعار الدائن</h3>
                @elseif($invoice->invoice_type === '381')
                    <h3 style="color: #198754;">ZATCA Debit Note Status / حالة إشعار المدين</h3>
                @else
                    <h3>ZATCA Invoice Status / حالة الفاتورة</h3>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span>
                        @if($invoice->zatca_status === 'submitted' || $invoice->zatca_status === 'reported')
                            <strong style="color: green;">✓ Reported Successfully</strong>
                        @elseif($invoice->zatca_status === 'cleared')
                            <strong style="color: green;">✓ Cleared Successfully</strong>
                        @elseif($invoice->zatca_status === 'failed')
                            <strong style="color: red;">✗ Submission Failed</strong>
                        @else
                            <strong style="color: orange;">⚠ Not Submitted</strong>
                        @endif
                    </span>
                </div>
                @if($invoice->zatca_uuid)
                <div class="detail-row">
                    <span class="detail-label">ZATCA UUID:</span>
                    <span>{{ $invoice->zatca_uuid }}</span>
                </div>
                @endif
                @if($invoice->current_hash)
                <div class="detail-row">
                    <span class="detail-label">Invoice Hash:</span>
                    <span style="font-family: monospace; font-size: 10px;">{{ $invoice->current_hash }}</span>
                </div>
                @endif
                @if($invoice->zatca_response && isset($invoice->zatca_response['validationResults']))
                    @php $validation = $invoice->zatca_response['validationResults']; @endphp
                    <div class="detail-row">
                        <span class="detail-label">Validation:</span>
                        <span>
                            @if($validation['status'] === 'PASS')
                                <strong style="color: green;">✓ PASS</strong>
                            @elseif($validation['status'] === 'WARNING')
                                <strong style="color: orange;">⚠ WARNING</strong>
                            @else
                                <strong style="color: red;">✗ ERROR</strong>
                            @endif
                        </span>
                    </div>
                    @if(isset($validation['infoMessages']) && count($validation['infoMessages']) > 0)
                    <div class="detail-row">
                        <span class="detail-label">Info:</span>
                        <span style="font-size: 10px; color: #666;">{{ $validation['infoMessages'][0]['message'] ?? 'Complied with ZATCA specifications' }}</span>
                    </div>
                    @endif
                @endif
            </div>
            
            @if($invoice->qr_code)
            <div style="text-align: center; margin-left: 20px;">
                <h4 style="margin-bottom: 10px; font-size: 12px;">QR Code / رمز الاستجابة السريعة</h4>
                <div style="border: 2px solid #333; padding: 10px; background: white; display: inline-block;">
                    <img src="data:image/png;base64,{{ app('DNS2D')->getBarcodePNG($invoice->qr_code, 'QRCODE', 4, 4) }}" 
                         alt="QR Code" 
                         style="width: 120px; height: 120px; display: block;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display: none; width: 120px; height: 120px; border: 1px dashed #ccc; text-align: center; line-height: 60px; font-size: 10px; color: #666;">
                        QR Code<br>Not Available<br><small>{{ substr($invoice->qr_code, 0, 20) }}...</small>
                    </div>
                </div>
                <p style="font-size: 10px; margin-top: 5px; color: #666;">Scan to verify invoice authenticity</p>
                {{-- Debug info (remove in production) --}}
                <div class="no-print" style="font-size: 8px; color: #999; margin-top: 5px;">
                    QR Length: {{ strlen($invoice->qr_code ?? '') }}<br>
                    QR Preview: {{ substr($invoice->qr_code ?? '', 0, 50) }}...
                </div>
            </div>
            @endif
        </div>
    </div>

    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
        Generated on {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>