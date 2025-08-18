<?php

namespace App\Http\Controllers;

use App\Models\ZatcaInvoice;
use App\Models\CertificateInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DebitNoteController extends Controller
{
    public function index()
    {
        $debitNotes = ZatcaInvoice::where('invoice_type', '383')
            ->with('certificate')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('zatca.debits.index', compact('debitNotes'));
    }

    public function create(Request $request)
    {
        $certificates = CertificateInfo::where('status', 'active')->get();
        $originalInvoice = null;

        // If creating debit note from a specific invoice
        if ($request->has('original_invoice_id')) {
            $originalInvoice = ZatcaInvoice::find($request->original_invoice_id);
        }

        return view('zatca.debits.create', compact('certificates', 'originalInvoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'certificate_info_id' => 'required|exists:certificate_infos,id',
            'original_invoice_id' => 'nullable|exists:zatca_invoices,id',
            'original_invoice_number' => 'required|string',
            'debit_reason' => 'required|string',
            'invoice_subtype' => 'required|in:01,02',
            'issue_date' => 'required|date',
            'issue_time' => 'required|string',
            'currency' => 'required|string',
            'buyer_info' => 'nullable|array',
            'line_items' => 'required|array|min:1',
            'line_items.*.name' => 'required|string',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit_price' => 'required|numeric|min:0.01',
            'line_items.*.tax_rate' => 'required|numeric|min:0|max:100',
            'debit_type' => 'required|in:additional_charges,price_correction,extra_services',
        ]);

        // Set default currency if not provided
        if (empty($validated['currency'])) {
            $validated['currency'] = 'SAR';
        }

        try {
            $certificate = CertificateInfo::findOrFail($validated['certificate_info_id']);
            
            // Generate invoice number for debit note
            $invoiceNumber = 'DN-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Process line items for debit notes (positive amounts)
            $processedItems = $this->processDebitItems($validated['line_items']);
            $totals = $this->calculateDebitTotals($processedItems);

            // Extract VAT from certificate serial number (format: 1-TST|2-VAT|3-NUM)
            $sellerVat = null;
            if ($certificate->serial_number && strpos($certificate->serial_number, '|2-') !== false) {
                $parts = explode('|', $certificate->serial_number);
                foreach ($parts as $part) {
                    if (str_starts_with($part, '2-')) {
                        $vatCandidate = substr($part, 2);
                        if (strlen($vatCandidate) === 15 && str_starts_with($vatCandidate, '3') && str_ends_with($vatCandidate, '3')) {
                            $sellerVat = $vatCandidate;
                            break;
                        }
                    }
                }
            }
            
            // Use the extracted VAT or fallback to certificate data
            if (!$sellerVat) {
                $sellerVat = $certificate->vat_number ?? '312844309300003'; // Use certificate VAT or real production VAT
            }
            
            $sellerInfo = [
                'name' => $certificate->organization_name ?? 'شركة ازدهار الصحراء للتعمير',
                'vat_number' => $sellerVat,
                'address' => $certificate->address ?? 'الرياض، العليا، المملكة العربية السعودية'
            ];

            // Create debit note
            $debitNote = ZatcaInvoice::create([
                'certificate_info_id' => $validated['certificate_info_id'],
                'invoice_number' => $invoiceNumber,
                'uuid' => Str::uuid()->toString(),
                'invoice_type' => '383', // Debit Note
                'invoice_subtype' => $validated['invoice_subtype'],
                'issue_date' => Carbon::parse($validated['issue_date']),
                'issue_time' => $validated['issue_time'],
                'icv' => $certificate->getNextICV(),
                'previous_invoice_hash' => $this->getPreviousInvoiceHash($certificate),
                'seller_info' => $sellerInfo,
                'buyer_info' => $validated['buyer_info'] ?? $this->getDefaultBuyerInfo(),
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total_amount'],
                'currency' => $validated['currency'],
                'line_items' => $processedItems,
                'tax_breakdown' => $this->generateTaxBreakdown($processedItems),
                'zatca_status' => 'pending',
                // Debit-specific fields
                'debit_reason' => $validated['debit_reason'],
                'original_invoice_number' => $validated['original_invoice_number'],
                'debit_type' => $validated['debit_type'],
            ]);

            // Link to original invoice if provided
            if ($validated['original_invoice_id']) {
                $originalInvoice = ZatcaInvoice::find($validated['original_invoice_id']);
                if ($originalInvoice) {
                    $debitNote->update([
                        'original_invoice_id' => $originalInvoice->id,
                        'buyer_info' => $originalInvoice->buyer_info,
                    ]);
                }
            }

            return redirect()->route('zatca.debits.show', $debitNote)
                ->with('success', 'Debit Note created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating debit note: ' . $e->getMessage());
        }
    }

    public function show(ZatcaInvoice $debitNote)
    {
        // Ensure this is a debit note
        if ($debitNote->invoice_type !== '383') {
            abort(404, 'Debit note not found');
        }

        $originalInvoice = null;
        if ($debitNote->original_invoice_id) {
            $originalInvoice = ZatcaInvoice::find($debitNote->original_invoice_id);
        }

        return view('zatca.debits.show', compact('debitNote', 'originalInvoice'));
    }

    public function createFromInvoice(ZatcaInvoice $invoice)
    {
        $certificates = CertificateInfo::where('status', 'active')->get();
        
        return view('zatca.debits.create', [
            'certificates' => $certificates,
            'originalInvoice' => $invoice
        ]);
    }

    public function generateDebitXML(ZatcaInvoice $debitNote)
    {
        if ($debitNote->invoice_type !== '383') {
            return response()->json([
                'success' => false,
                'message' => 'This is not a debit note'
            ], 400);
        }

        try {
            // Use the same XML generation logic as regular invoices
            $controller = new ZatcaInvoiceController();
            return $controller->generateXML($debitNote);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating debit note XML: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processDebit(Request $request, ZatcaInvoice $debitNote)
    {
        $validated = $request->validate([
            'action' => 'required|in:sign,submit,generate_qr'
        ]);

        try {
            $controller = new ZatcaInvoiceController();
            
            switch ($validated['action']) {
                case 'sign':
                    return $controller->signInvoice($debitNote);
                case 'submit':
                    return $controller->submitToZatca($debitNote);
                case 'generate_qr':
                    return $controller->generateQRCode($debitNote);
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid action'], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing debit note: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processDebitItems($items)
    {
        $processedItems = [];
        
        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $taxRate = (float) $item['tax_rate'];
            
            // Calculate amounts (positive for debit notes)
            $lineTotal = $quantity * $unitPrice;
            $taxAmount = ($lineTotal * $taxRate) / 100;
            $totalWithTax = $lineTotal + $taxAmount;
            
            $processedItems[] = [
                'name' => $item['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'line_total' => $lineTotal,
                'tax_amount' => $taxAmount,
                'total_with_tax' => $totalWithTax,
                'total_amount' => $totalWithTax,
            ];
        }
        
        return $processedItems;
    }

    private function calculateDebitTotals($items)
    {
        $subtotal = 0;
        $taxAmount = 0;
        
        foreach ($items as $item) {
            $subtotal += $item['line_total'];
            $taxAmount += $item['tax_amount'];
        }
        
        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
        ];
    }

    private function generateTaxBreakdown($items)
    {
        $taxBreakdown = [];
        
        foreach ($items as $item) {
            $rate = $item['tax_rate'];
            
            if (!isset($taxBreakdown[$rate])) {
                $taxBreakdown[$rate] = [
                    'rate' => $rate,
                    'taxable_amount' => 0,
                    'tax_amount' => 0,
                ];
            }
            
            $taxBreakdown[$rate]['taxable_amount'] += $item['line_total'];
            $taxBreakdown[$rate]['tax_amount'] += $item['tax_amount'];
        }
        
        return array_values($taxBreakdown);
    }

    private function getPreviousInvoiceHash($certificate)
    {
        $lastInvoice = ZatcaInvoice::where('certificate_info_id', $certificate->id)
            ->whereNotNull('current_hash')
            ->orderBy('created_at', 'desc')
            ->first();
            
        return $lastInvoice ? $lastInvoice->current_hash : 
            'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';
    }

    private function getDefaultBuyerInfo()
    {
        return [
            'name' => 'شركة نماذج فاتورة المحدودة | Fatoora Samples LTD',
            'vat_number' => '399999999800003',
            'address' => 'صلاح الدين | Salah Al-Din, الرياض | Riyadh'
        ];
    }

    public function print(ZatcaInvoice $debitNote)
    {
        // Ensure this is a debit note
        if ($debitNote->invoice_type !== '383') {
            abort(404, 'Debit note not found');
        }

        return view('zatca.debits.print', compact('debitNote'));
    }

    public function destroy(ZatcaInvoice $debitNote)
    {
        // Ensure this is a debit note and it's still pending
        if ($debitNote->invoice_type !== '383') {
            return redirect()->back()->with('error', 'Not a valid debit note.');
        }

        if (!$debitNote->isPending()) {
            return redirect()->back()->with('error', 'Cannot delete a processed debit note.');
        }

        $debitNote->delete();

        return redirect()->route('zatca.debits.index')
            ->with('success', 'Debit note deleted successfully.');
    }
}