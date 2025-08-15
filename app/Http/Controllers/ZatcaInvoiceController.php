<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateInfo;
use App\Models\ZatcaInvoice;
use App\Zatca\Helpers\ApiHelper;
use App\Zatca\Helpers\InvoiceHelper;
use App\Zatca\Helpers\XMLInvoiceBuilder;
use App\Zatca\Signer\EInvoiceSigner;
use App\Zatca\Signer\QRCodeGenerator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
class ZatcaInvoiceController extends Controller
{

    public function index()
    {
        $invoices = ZatcaInvoice::with('certificate')
                               ->orderBy('created_at', 'desc')
                               ->paginate(15);
        return view('zatca.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $certificates = CertificateInfo::where('status', 'active')->get();
        return view('zatca.invoices.create', compact('certificates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'certificate_info_id' => 'required|exists:certificate_infos,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_type' => 'required|in:388,381,383',
            'invoice_subtype' => 'required|in:01,02',
            'issue_date' => 'required|date',
            'issue_time' => 'required',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'seller_name' => 'required|string|max:255',
            'seller_vat' => 'required|string|max:20',
            'seller_address' => 'required|string',
            'buyer_name' => 'nullable|string|max:255',
            'buyer_vat' => 'nullable|string|max:20',
            'buyer_address' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'line_items' => 'required|array|min:1',
            'line_items.*.name' => 'required|string|max:255',
            'line_items.*.quantity' => 'required|numeric|min:0',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.tax_rate' => 'required|numeric|min:0',
        ]);

        $certificate = CertificateInfo::findOrFail($request->certificate_info_id);
        
        // Calculate totals
        $subtotal = 0;
        $taxAmount = 0;
        $lineItems = [];

        foreach ($request->line_items as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $lineTax = $lineTotal * ($item['tax_rate'] / 100);
            
            $lineItems[] = [
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $lineTax,
                'total_with_tax' => $lineTotal + $lineTax
            ];
            
            $subtotal += $lineTotal;
            $taxAmount += $lineTax;
        }

        $invoice = ZatcaInvoice::create([
            'certificate_info_id' => $certificate->id,
            'invoice_number' => $request->invoice_number,
            'uuid' => Str::uuid()->toString(),
            'invoice_type' => $request->invoice_type,
            'invoice_subtype' => $request->invoice_subtype,
            'issue_date' => $request->issue_date,
            'issue_time' => $request->issue_time,
            'due_date' => $request->due_date,
            'icv' => $certificate->getNextICV(),
            'previous_invoice_hash' => $certificate->lastInvoiceHash ?: 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
            'seller_info' => [
                'name' => $request->seller_name,
                'vat_number' => $request->seller_vat,
                'address' => $request->seller_address,
            ],
            'buyer_info' => $request->buyer_name ? [
                'name' => $request->buyer_name,
                'vat_number' => $request->buyer_vat,
                'address' => $request->buyer_address,
            ] : null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'total_amount' => $subtotal + $taxAmount,
            'currency' => $request->currency,
            'line_items' => $lineItems,
        ]);

        return redirect()->route('zatca.invoices.show', $invoice)
                        ->with('success', 'Invoice created successfully.');
    }

    public function show(ZatcaInvoice $invoice)
    {
        $invoice->load('certificate');
        return view('zatca.invoices.show', compact('invoice'));
    }

    public function generateXML(ZatcaInvoice $invoice)
    {
        try {
            $builder = new XMLInvoiceBuilder();
            
            // Set basic invoice information
            $isSimplified = $invoice->invoice_subtype === '02';
            $invoiceTypeName = $isSimplified ? '0200000' : '0100000';
            
            $builder->setBasicInfo(
                'reporting:1.0',
                $invoice->invoice_number,
                $invoice->uuid,
                $invoice->issue_date->format('Y-m-d'),
                $invoice->issue_time,
                $invoice->invoice_type,
                $invoiceTypeName,
                $invoice->currency
            );

            // Add note if needed
            $builder->addNote('ABC');
            
            // Set currency codes
            $builder->setCurrencyCodes($invoice->currency);
            
            // Add ICV and PIH references
            $builder->addAdditionalDocumentReference('ICV', null, (string)$invoice->icv);
            $builder->addAdditionalDocumentReference('PIH', null, $invoice->previous_invoice_hash);
            
            // Add QR code placeholder
            $builder->addQRCode('BASE64_QRCODE_PLACEHOLDER');
            
            // Add signature placeholder
            $builder->addSignature();

            // Set supplier party
            $sellerAddress = [
                'streetName' => $invoice->seller_info['address'] ?? 'N/A',
                'buildingNumber' => '0000',
                'citySubdivisionName' => 'N/A',
                'cityName' => 'Riyadh',
                'postalZone' => '00000',
                'countryCode' => 'SA'
            ];
            
            $builder->setSupplierParty(
                '1010010000',
                $invoice->seller_info['vat_number'],
                $invoice->seller_info['name'],
                $sellerAddress
            );

            // Set customer party (if not simplified)
            if (!$isSimplified && $invoice->buyer_info) {
                $buyerAddress = [
                    'streetName' => $invoice->buyer_info['address'] ?? 'N/A',
                    'buildingNumber' => '0000',
                    'citySubdivisionName' => 'N/A',
                    'cityName' => 'Riyadh',
                    'postalZone' => '00000',
                    'countryCode' => 'SA'
                ];
                
                $builder->setCustomerParty(
                    null,
                    $invoice->buyer_info['vat_number'] ?? null,
                    $invoice->buyer_info['name'],
                    $buyerAddress
                );
            }

            // Set delivery
            $builder->setDelivery($invoice->issue_date->format('Y-m-d'));
            
            // Set payment means
            $builder->setPaymentMeans('10');
            
            // Add allowance/charge
            $builder->addAllowanceCharge(false, 'discount', '0.00');

            // Add tax totals
            $taxSubtotals = [
                [
                    'taxableAmount' => number_format($invoice->subtotal, 2),
                    'taxAmount' => number_format($invoice->tax_amount, 2),
                    'categoryId' => 'S',
                    'percent' => 15.00
                ]
            ];

            $builder->addTaxTotal(number_format($invoice->tax_amount, 2), $invoice->currency);
            $builder->addTaxTotal(number_format($invoice->tax_amount, 2), $invoice->currency, $taxSubtotals);

            // Set legal monetary total
            $builder->setLegalMonetaryTotal(
                number_format($invoice->subtotal, 2),
                number_format($invoice->subtotal, 2),
                number_format($invoice->total_amount, 2),
                '0.00',
                '0.00',
                number_format($invoice->total_amount, 2),
                $invoice->currency
            );

            // Add invoice lines
            foreach ($invoice->line_items as $index => $item) {
                $lineTotal = $item['line_total'];
                $lineTax = $item['tax_amount'];
                $totalWithTax = $item['total_with_tax'];
                
                $builder->addInvoiceLine(
                    (string)($index + 1),
                    $item['quantity'],
                    'PCE',
                    number_format($lineTotal, 2),
                    $item['name'],
                    number_format($item['unit_price'], 2),
                    number_format($lineTax, 2),
                    number_format($totalWithTax, 2),
                    'S',
                    $item['tax_rate'],
                    [['isCharge' => false, 'reason' => 'discount', 'amount' => '0.00']],
                    $invoice->currency
                );
            }

            // Get the XML
            $xml = $builder->getXML();
            
            $invoice->update(['invoice_xml' => $xml]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice XML generated successfully',
                'xml' => $xml
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate XML: ' . $e->getMessage()
            ], 500);
        }
    }

    public function signInvoice(ZatcaInvoice $invoice)
    {
        try {
            if (!$invoice->invoice_xml) {
                return response()->json([
                    'success' => false,
                    'message' => 'Generate XML first'
                ], 400);
            }

            // Create DOMDocument from XML string
            $dom = new \DOMDocument();
            $dom->loadXML($invoice->invoice_xml);

            // Get certificate content (try production first, then compliance)
            $certificate = $invoice->certificate;
            $x509CertificateContent = base64_decode(
                $certificate->pcsid_binarySecurityToken ?: $certificate->ccsid_binarySecurityToken
            );

            // Sign the invoice using GetRequestApi
            $signedJsonResult = EInvoiceSigner::GetRequestApi(
                $dom,
                $x509CertificateContent,
                $certificate->private_key
            );

            // Parse the JSON result
            $signedData = json_decode($signedJsonResult, true);
            
            if (!$signedData) {
                throw new Exception('Failed to parse signed invoice data');
            }

            // Extract signed XML from base64
            $signedXml = base64_decode($signedData['invoice']);
            $invoiceHash = $signedData['invoiceHash'];
            
            $invoice->update([
                'signed_xml' => $signedXml,
                'current_hash' => $invoiceHash
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice signed successfully',
                'hash' => $invoiceHash
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sign invoice: ' . $e->getMessage()
            ], 500);
        }
    }


public function submitToZatca(ZatcaInvoice $invoice)
{
    try {
        if (!$invoice->signed_xml) {
            return response()->json([
                'success' => false,
                'message' => 'Sign invoice first'
            ], 400);
        }

        $certificate = $invoice->certificate;

        // Determine if this is a simplified invoice (subtype 02)
        $isSimplified = $invoice->invoice_subtype === '02';
        
        // Prepare certificate info array for API call
        $certInfoArray = $certificate->toArray();

        // Prepare the signed invoice as JSON payload
        $jsonPayload = json_encode([
            'invoiceHash' => $invoice->current_hash,
            'invoice' => base64_encode($invoice->signed_xml)
        ]);

        // Send to appropriate ZATCA endpoint
        if ($isSimplified) {
            $response = ApiHelper::invoiceReporting($certInfoArray, $jsonPayload);
            $statusKey = 'reportingStatus';
        } else {
            $response = ApiHelper::invoiceClearance($certInfoArray, $jsonPayload);
            $statusKey = 'clearanceStatus';
        }

        // Parse response
        $jsonDecodedResponse = json_decode($response, true);

        if (!$jsonDecodedResponse) {
            throw new Exception('Invalid JSON response from ZATCA API');
        }

        // Determine status based on response
        $status = $jsonDecodedResponse[$statusKey] ?? 'UNKNOWN';
        $isSuccess = (strpos($status, 'REPORTED') !== false || strpos($status, 'CLEARED') !== false);

        // Save result
        $invoice->update([
            'zatca_status'    => $isSuccess ? ($isSimplified ? 'reported' : 'cleared') : 'failed',
            'zatca_response'  => $jsonDecodedResponse,
            'zatca_uuid'      => $jsonDecodedResponse['uuid'] ?? null,
            'submitted_at'    => now(),
            'cleared_at'      => !$isSimplified && $isSuccess ? now() : null,
            'error_message'   => !$isSuccess ? ($jsonDecodedResponse['message'] ?? 'Submission failed') : null
        ]);

        if ($isSuccess) {
            $certificate->updateLastInvoiceHash($invoice->current_hash);
        }

        return response()->json([
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Invoice submitted successfully' : 'Invoice submission failed',
            'status'  => $status,
            'data'    => $jsonDecodedResponse
        ]);

    } catch (\Exception $e) {
        $invoice->update([
            'zatca_status'  => 'failed',
            'error_message' => $e->getMessage()
        ]);

        Log::error('ZATCA Submission Failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to submit to ZATCA: ' . $e->getMessage()
        ], 500);
    }
}



    public function generateQRCode(ZatcaInvoice $invoice)
    {
        try {
            if (!$invoice->signed_xml) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice must be signed before generating QR code'
                ], 400);
            }

            if (!$invoice->current_hash) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice hash not found. Sign invoice first.'
                ], 400);
            }

            // Load signed XML to extract signature value
            $dom = new \DOMDocument();
            $dom->loadXML($invoice->signed_xml);
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
            
            $signatureValueNode = $xpath->query('//ds:SignatureValue')->item(0);
            $signatureValue = $signatureValueNode ? $signatureValueNode->nodeValue : '';

            // Get certificate content (try production first, then compliance)
            $certificate = $invoice->certificate;
            $x509CertificateContent = base64_decode(
                $certificate->pcsid_binarySecurityToken ?: $certificate->ccsid_binarySecurityToken
            );

            // Generate QR code using the static method
            $qrCode = QRCodeGenerator::generateQRCode(
                $invoice->signed_xml,
                $invoice->current_hash,
                $signatureValue,
                $x509CertificateContent
            );
            
            $invoice->update(['qr_code' => $qrCode]);

            return response()->json([
                'success' => true,
                'message' => 'QR code generated successfully',
                'qr_code' => $qrCode
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ZatcaInvoice $invoice)
    {
        if ($invoice->zatca_status === 'cleared' || $invoice->zatca_status === 'reported') {
            return redirect()->back()->with('error', 'Cannot delete a submitted invoice.');
        }

        $invoice->delete();
        return redirect()->route('zatca.invoices.index')
                        ->with('success', 'Invoice deleted successfully.');
    }
}
