<?php

namespace App\Http\Controllers;

use App\Models\ZatcaInvoice;
use App\Models\CertificateInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReturnInvoiceController extends Controller
{
    public function index()
    {
        $returnInvoices = ZatcaInvoice::where('invoice_type', '381')
            ->with('certificate')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('zatca.returns.index', compact('returnInvoices'));
    }

    public function create(Request $request)
    {
        $certificates = CertificateInfo::where('status', 'active')->get();
        $originalInvoice = null;

        // If returning from a specific invoice
        if ($request->has('original_invoice_id')) {
            $originalInvoice = ZatcaInvoice::find($request->original_invoice_id);
        }

        return view('zatca.returns.create', compact('certificates', 'originalInvoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'certificate_info_id' => 'required|exists:certificate_infos,id',
            'original_invoice_id' => 'nullable|exists:zatca_invoices,id',
            'original_invoice_number' => 'required|string',
            'return_reason' => 'required|string',
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
            'return_type' => 'required|in:full,partial',
        ]);
        
        // Set default currency if not provided
        if (empty($validated['currency'])) {
            $validated['currency'] = 'SAR';
        }

        try {
            $certificate = CertificateInfo::findOrFail($validated['certificate_info_id']);
            
            // Generate invoice number for credit note
            $invoiceNumber = 'CN-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Process line items for returns (negative amounts)
            $processedItems = $this->processReturnItems($validated['line_items']);
            $totals = $this->calculateReturnTotals($processedItems);

            // Get seller info from certificate
            $sellerInfo = [
                'name' => $certificate->organization_name,
                'vat_number' => $certificate->organization_unit_name ?? 'N/A',
                'address' => 'Riyadh, Saudi Arabia'
            ];

            // Create return invoice (credit note)
            $returnInvoice = ZatcaInvoice::create([
                'certificate_info_id' => $validated['certificate_info_id'],
                'invoice_number' => $invoiceNumber,
                'uuid' => Str::uuid()->toString(),
                'invoice_type' => '381', // Credit Note
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
                // Return-specific fields
                'return_reason' => $validated['return_reason'],
                'original_invoice_number' => $validated['original_invoice_number'],
                'return_type' => $validated['return_type'],
            ]);

            // Link to original invoice if provided
            if ($validated['original_invoice_id']) {
                $originalInvoice = ZatcaInvoice::find($validated['original_invoice_id']);
                if ($originalInvoice) {
                    $returnInvoice->update([
                        'original_invoice_id' => $originalInvoice->id,
                        'buyer_info' => $originalInvoice->buyer_info,
                    ]);
                }
            }

            return redirect()->route('zatca.returns.show', $returnInvoice)
                ->with('success', 'Return invoice (Credit Note) created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating return invoice: ' . $e->getMessage());
        }
    }

    public function show(ZatcaInvoice $returnInvoice)
    {
        // Ensure this is a credit note
        if ($returnInvoice->invoice_type !== '381') {
            abort(404, 'Return invoice not found');
        }

        $originalInvoice = null;
        if ($returnInvoice->original_invoice_id) {
            $originalInvoice = ZatcaInvoice::find($returnInvoice->original_invoice_id);
        }

        return view('zatca.returns.show', compact('returnInvoice', 'originalInvoice'));
    }

    public function createFromInvoice(ZatcaInvoice $invoice)
    {
        $certificates = CertificateInfo::where('status', 'active')->get();
        
        return view('zatca.returns.create', [
            'certificates' => $certificates,
            'originalInvoice' => $invoice
        ]);
    }

    public function generateReturnXML(ZatcaInvoice $returnInvoice)
    {
        if ($returnInvoice->invoice_type !== '381') {
            return response()->json([
                'success' => false,
                'message' => 'This is not a return invoice'
            ], 400);
        }

        try {
            // Use the same XML generation logic as regular invoices
            $controller = new ZatcaInvoiceController();
            return $controller->generateXML($returnInvoice);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating return invoice XML: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processReturn(Request $request, ZatcaInvoice $returnInvoice)
    {
        $validated = $request->validate([
            'action' => 'required|in:sign,submit,generate_qr'
        ]);

        try {
            $controller = new ZatcaInvoiceController();
            
            switch ($validated['action']) {
                case 'sign':
                    return $controller->signInvoice($returnInvoice);
                case 'submit':
                    return $controller->submitToZatca($returnInvoice);
                case 'generate_qr':
                    return $controller->generateQRCode($returnInvoice);
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid action'], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing return: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processReturnItems($items)
    {
        $processedItems = [];
        
        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $taxRate = (float) $item['tax_rate'];
            
            // Calculate amounts (negative for returns)
            $lineTotal = $quantity * $unitPrice;
            $taxAmount = ($lineTotal * $taxRate) / 100;
            $totalWithTax = $lineTotal + $taxAmount;
            
            $processedItems[] = [
                'name' => $item['name'],
                'quantity' => -$quantity, // Negative quantity for returns
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'line_total' => -$lineTotal, // Negative amount
                'tax_amount' => -$taxAmount, // Negative tax
                'total_with_tax' => -$totalWithTax, // Negative total with tax
                'total_amount' => -$totalWithTax, // Negative total (backward compatibility)
            ];
        }
        
        return $processedItems;
    }

    private function calculateReturnTotals($items)
    {
        $subtotal = 0;
        $taxAmount = 0;
        
        foreach ($items as $item) {
            $subtotal += $item['line_total']; // Already negative
            $taxAmount += $item['tax_amount']; // Already negative
        }
        
        return [
            'subtotal' => $subtotal, // Negative value
            'tax_amount' => $taxAmount, // Negative value
            'total_amount' => $subtotal + $taxAmount, // Negative total
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

    public function destroy(ZatcaInvoice $returnInvoice)
    {
        // Ensure this is a credit note and it's still pending
        if ($returnInvoice->invoice_type !== '381') {
            return redirect()->back()->with('error', 'Not a valid return invoice.');
        }

        if (!$returnInvoice->isPending()) {
            return redirect()->back()->with('error', 'Cannot delete a processed return invoice.');
        }

        $returnInvoice->delete();

        return redirect()->route('zatca.returns.index')
            ->with('success', 'Return invoice deleted successfully.');
    }
}