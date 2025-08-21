<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyInvoice;
use App\Models\CompanyZatcaOnboarding;
use App\Models\CompanyInvoiceLineItem;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class CompanyInvoiceReturnController extends Controller
{
    /**
     * Display a listing of return invoices (credit notes)
     */
    public function index()
    {
        $invoices = CompanyInvoice::with(['company'])
                               ->where('invoice_type', '383') // Credit notes only
                               ->orderBy('created_at', 'desc')
                               ->paginate(15);
        return view('zatca.company.returns.index', compact('invoices'));
    }

    /**
     * Show the form for creating a return invoice from an original invoice
     */
    public function create(CompanyInvoice $invoice)
    {
        // Only allow returns for submitted invoices
        if (!$invoice->isSubmitted()) {
            return redirect()->back()->with('error', 'Can only create returns for submitted invoices.');
        }

        $companies = CompanyZatcaOnboarding::where('status', 'success')->get();
        $customers = \App\Models\Customer::active()->orderBy('name')->get();
        
        return view('zatca.company.invoices.create-return', compact('invoice', 'companies', 'customers'));
    }

    /**
     * Store a newly created return invoice (credit note)
     */
    public function store(Request $request)
    {
        // Validation rules specific to return invoices - customer required
        $rules = [
            'company_zatca_onboarding_id' => 'required|exists:company_zatca_onboarding,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_subtype' => 'required|in:01,02',
            'issue_date' => 'required|date',
            'issue_time' => 'required',
            'currency' => 'required|string|size:3',
            'return_reason' => 'required|string|max:255',
            'original_invoice_number' => 'required|string|max:255',
            'original_invoice_id' => 'nullable|exists:company_invoices,id',
            'line_items' => 'required|array|min:1',
            'line_items.*.product_id' => 'required|exists:products,id',
            'line_items.*.name' => 'required|string|max:255',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.tax_rate' => 'required|numeric|min:0|max:100',
        ];

        $request->validate($rules);

        DB::beginTransaction();
        
        try {
            $company = CompanyZatcaOnboarding::findOrFail($request->company_zatca_onboarding_id);
            
            // CRITICAL: Use the exact VAT number from company serial number for ZATCA compliance
            $sellerVat = $this->extractVatFromSerialNumber($company);
            
            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;
            $lineItemsData = [];

            foreach ($request->line_items as $item) {
                // Skip items with zero quantity (for return forms where items are not selected)
                if (!isset($item['quantity']) || $item['quantity'] == 0) {
                    continue;
                }

                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = $lineTotal * ($item['tax_rate'] / 100);
                
                // Get product details for proper line item data
                $product = Product::findOrFail($item['product_id']);
                
                // Return/credit note line items (negative amounts for returns)
                $lineItemsData[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'sku' => $product->sku,
                    'quantity' => -$item['quantity'], // Negative for return
                    'unit_price' => $item['unit_price'],
                    'line_total' => -$lineTotal, // Negative for return
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => -$lineTax, // Negative for return
                    'total_with_tax' => -($lineTotal + $lineTax), // Negative for return
                    'unit_of_measure' => $product->unit_of_measure
                ];
                
                $subtotal += -$lineTotal; // Negative subtotal for return
                $taxAmount += -$lineTax; // Negative tax for return
            }

            // Prepare invoice data for credit note (383)
            $invoiceData = [
                'company_zatca_onboarding_id' => $company->id,
                'customer_id' => $request->customer_id,
                'invoice_number' => $request->invoice_number,
                'uuid' => Str::uuid()->toString(),
                'invoice_type' => '383', // Credit Note
                'invoice_subtype' => $request->invoice_subtype,
                'issue_date' => $request->issue_date,
                'issue_time' => $request->issue_time,
                'due_date' => null,
                'icv' => $company->getNextICV(),
                'previous_invoice_hash' => $company->lastInvoiceHash ?: 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total_amount' => $subtotal + $taxAmount,
                'currency' => $request->currency,
                // Return-specific fields
                'return_reason' => $request->return_reason,
                'original_invoice_number' => $request->original_invoice_number,
                'original_invoice_id' => $request->original_invoice_id,
            ];

            $invoice = CompanyInvoice::create($invoiceData);

            // Save line items to separate table
            foreach ($request->line_items as $item) {
                // Skip items with zero quantity
                if (!isset($item['quantity']) || $item['quantity'] == 0) {
                    continue;
                }

                CompanyInvoiceLineItem::create([
                    'company_invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'], // Use the original product_id
                    'quantity' => -$item['quantity'], // Negative for return
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => 0, // No additional discount on returns
                    'discount_percentage' => 0, // No additional discount on returns
                    'tax_rate' => $item['tax_rate']
                ]);
            }

            DB::commit();

            return redirect()->route('zatca.company.invoices.show', $invoice)
                ->with('success', 'Return invoice (Credit Note) created successfully.');

        } catch (Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified return invoice
     */
    public function show(CompanyInvoice $returnInvoice)
    {
        // Ensure this is a credit note
        if ($returnInvoice->invoice_type !== '383') {
            abort(404, 'Return invoice not found');
        }

        $returnInvoice->load(['company', 'customer', 'lineItems.product']);
        
        $originalInvoice = null;
        if ($returnInvoice->original_invoice_id) {
            $originalInvoice = CompanyInvoice::with(['company', 'customer', 'lineItems.product'])
                                           ->find($returnInvoice->original_invoice_id);
        }

        return view('zatca.company.invoices.show', compact('returnInvoice', 'originalInvoice'));
    }

    /**
     * Remove the specified return invoice from storage
     */
    public function destroy(CompanyInvoice $returnInvoice)
    {
        // Ensure this is a credit note and it's still pending
        if ($returnInvoice->invoice_type !== '383') {
            return redirect()->back()->with('error', 'Not a valid return invoice.');
        }

        if (!$returnInvoice->isPending()) {
            return redirect()->back()->with('error', 'Cannot delete a processed return invoice.');
        }

        DB::beginTransaction();
        
        try {
            // Delete line items first
            $returnInvoice->lineItems()->delete();
            
            // Delete the invoice
            $returnInvoice->delete();
            
            DB::commit();

            return redirect()->route('zatca.company.returns.index')
                ->with('success', 'Return invoice deleted successfully.');
                
        } catch (Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Error deleting return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Extract VAT number from company serial number
     */
    private function extractVatFromSerialNumber($company)
    {
        $sellerVat = null;
        
        // Extract VAT from company serial number (format: 1-TST|2-VAT|3-NUM)
        if ($company->serial_number && strpos($company->serial_number, '|2-') !== false) {
            $parts = explode('|', $company->serial_number);
            foreach ($parts as $part) {
                if (str_starts_with($part, '2-')) {
                    $sellerVat = substr($part, 2); // Extract VAT from serial number
                    break;
                }
            }
        }
        
        // Fallback only if no VAT found in serial number
        if (!$sellerVat) {
            $sellerVat = $company->vat_number ?? '399999999900003';
        }
        
        return $sellerVat;
    }
}