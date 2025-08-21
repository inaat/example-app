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
                <span>{{ $invoice->company->organization_name ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">VAT Number:</span>
                <span>{{ $invoice->company->vat_number ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span>{{ $invoice->company->registered_address ?? 'N/A' }}</span>
            </div>
            @if($invoice->company->email)
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span>{{ $invoice->company->email }}</span>
            </div>
            @endif
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

    @if($invoice->customer)
    <div class="detail-section" style="margin-bottom: 20px;">
        <h3>Customer Information / معلومات العميل</h3>
        <div class="detail-row">
            <span class="detail-label">Name:</span>
            <span>{{ $invoice->customer->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">VAT Number:</span>
            <span>{{ $invoice->customer->vat_number ?? 'N/A' }}</span>
        </div>
        @if($invoice->customer->email)
        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span>{{ $invoice->customer->email }}</span>
        </div>
        @endif
        @if($invoice->customer->phone)
        <div class="detail-row">
            <span class="detail-label">Phone:</span>
            <span>{{ $invoice->customer->phone }}</span>
        </div>
        @endif
        <div class="detail-row">
            <span class="detail-label">Address:</span>
            <span>{{ $invoice->customer->full_address ?? 'N/A' }}</span>
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
                @if($invoice->invoice_type === '388')
                    <th>Subtotal / المجموع الفرعي</th>
                    <th>Discount / الخصم</th>
                    <th>After Discount / بعد الخصم</th>
                @endif
                <th>Tax Rate / معدل الضريبة</th>
                @if($invoice->invoice_type === '388')
                    <th>Tax Amount / مبلغ الضريبة</th>
                @endif
                <th>Total / المجموع</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->lineItems && $invoice->lineItems->count() > 0)
                @foreach($invoice->lineItems as $index => $lineItem)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $lineItem->product->name ?? 'N/A' }}</td>
                    <td class="number">{{ number_format($lineItem->quantity, 2) }}</td>
                    <td class="number">{{ number_format($lineItem->unit_price, 2) }} {{ $invoice->currency }}</td>
                    @if($invoice->invoice_type === '388')
                        @php
                            $lineSubtotal = $lineItem->quantity * $lineItem->unit_price;
                            $discountAmount = $lineItem->discount_amount ?? 0;
                            $afterDiscount = $lineSubtotal - $discountAmount;
                            $taxAmount = $afterDiscount * ($lineItem->tax_rate / 100);
                        @endphp
                        <td class="number">{{ number_format($lineSubtotal, 2) }} {{ $invoice->currency }}</td>
                        <td class="number">
                            @if($lineItem->discount_amount > 0)
                                {{ number_format($lineItem->discount_amount, 2) }} {{ $invoice->currency }}
                            @elseif($lineItem->discount_percentage > 0)
                                {{ number_format($lineItem->discount_percentage, 1) }}%
                            @else
                                -
                            @endif
                        </td>
                        <td class="number">{{ number_format($afterDiscount, 2) }} {{ $invoice->currency }}</td>
                    @endif
                    <td class="number">{{ number_format($lineItem->tax_rate, 1) }}%</td>
                    @if($invoice->invoice_type === '388')
                        <td class="number">{{ number_format($taxAmount, 2) }} {{ $invoice->currency }}</td>
                    @endif
                    <td class="number">{{ number_format($lineItem->total_with_tax ?? ($afterDiscount + $taxAmount), 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ $invoice->invoice_type === '388' ? '10' : '6' }}" style="text-align: center; padding: 20px; color: #666;">
                        No items found for this invoice
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="totals">
        <table>
            @if($invoice->invoice_type === '388')
                @php
                    // Calculate totals from database line items for accuracy
                    $calculatedSubtotalBeforeDiscount = 0;
                    $calculatedLineDiscounts = 0;
                    $calculatedSubtotalAfterDiscount = 0;
                    $calculatedTaxAmount = 0;
                    $calculatedTotal = 0;
                    
                    if($invoice->lineItems && $invoice->lineItems->count() > 0) {
                        foreach($invoice->lineItems as $lineItem) {
                            $lineSubtotal = $lineItem->quantity * $lineItem->unit_price;
                            $discountAmount = $lineItem->discount_amount ?? 0;
                            $afterDiscount = $lineSubtotal - $discountAmount;
                            $taxAmount = $afterDiscount * ($lineItem->tax_rate / 100);
                            
                            $calculatedSubtotalBeforeDiscount += $lineSubtotal;
                            $calculatedLineDiscounts += $discountAmount;
                            $calculatedSubtotalAfterDiscount += $afterDiscount;
                            $calculatedTaxAmount += $taxAmount;
                            $calculatedTotal += $afterDiscount + $taxAmount;
                        }
                    }
                    
                    // Apply overall discount if any
                    $overallDiscount = $invoice->overall_discount_amount ?? 0;
                    $finalTotal = $calculatedTotal - $overallDiscount;
                @endphp
                
                @if($calculatedLineDiscounts > 0 || $overallDiscount > 0)
                <tr>
                    <td class="total-label">Subtotal Before Discounts / المجموع قبل الخصومات:</td>
                    <td class="number">{{ number_format($calculatedSubtotalBeforeDiscount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @if($calculatedLineDiscounts > 0)
                <tr>
                    <td class="total-label">Line Discounts / خصومات البنود:</td>
                    <td class="number">-{{ number_format($calculatedLineDiscounts, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endif
                @if($overallDiscount > 0)
                <tr>
                    <td class="total-label">
                        @if($invoice->overall_discount_percentage > 0)
                            Invoice Discount ({{ number_format($invoice->overall_discount_percentage, 1) }}%) / خصم الفاتورة:
                        @else
                            Invoice Discount / خصم الفاتورة:
                        @endif
                    </td>
                    <td class="number">-{{ number_format($overallDiscount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endif
                <tr>
                    <td class="total-label">Subtotal After Discounts / المجموع بعد الخصومات:</td>
                    <td class="number">{{ number_format($calculatedSubtotalAfterDiscount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @else
                <tr>
                    <td class="total-label">Subtotal / المجموع الفرعي:</td>
                    <td class="number">{{ number_format($calculatedSubtotalAfterDiscount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endif
                <tr>
                    <td class="total-label">Tax Amount / مبلغ الضريبة:</td>
                    <td class="number">{{ number_format($calculatedTaxAmount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                <tr class="final-total">
                    <td class="total-label">Total Amount / المبلغ الإجمالي:</td>
                    <td class="number">{{ number_format($finalTotal, 2) }} {{ $invoice->currency }}</td>
                </tr>
            @else
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
                    @endif
                </tr>
            @endif
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