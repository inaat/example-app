<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateInfo;
use App\Zatca\Helpers\CsrGenerator;
use App\Zatca\Helpers\ApiHelper;
use App\Zatca\Helpers\InvoiceHelper;
use App\Zatca\Signer\EInvoiceSigner;
use Illuminate\Support\Str;
use Exception;

class ZatcaOnboardingController extends Controller
{

    public function index()
    {
        $certificates = CertificateInfo::orderBy('created_at', 'desc')->paginate(10);
        return view('zatca.onboarding.index', compact('certificates'));
    }

    public function create()
    {
        return view('zatca.onboarding.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'organization_identifier' => 'required|string|max:255',
            'organization_name' => 'required|string|max:255',
            'organization_unit_name' => 'nullable|string|max:255',
            'common_name' => 'required|string|max:255',
            'country_name' => 'required|string|size:2',
            'location_address' => 'nullable|string|max:255',
            'business_category' => 'nullable|string|max:255',
            'environment_type' => 'required|in:NonProduction,Simulation,Production',
            'otp_used' => 'nullable|string|max:20',
        ]);

        // Generate serial number based on environment
        $prefix = match($request->environment_type) {
            'Production' => 'PRD',
            'Simulation' => 'SIM', 
            'NonProduction' => 'TST',
            default => 'TST'
        };
        
        $serialNumber = "1-{$prefix}|2-" . $request->organization_identifier . "|3-" . time();

        $certificate = CertificateInfo::create([
            'organization_identifier' => $request->organization_identifier,
            'organization_name' => $request->organization_name,
            'organization_unit_name' => $request->organization_unit_name,
            'common_name' => $request->common_name,
            'serial_number' => $serialNumber,
            'country_name' => $request->country_name,
            'location_address' => $request->location_address,
            'business_category' => $request->business_category,
            'environment_type' => $request->environment_type,
            'otp_used' => $request->otp_used,
            'status' => 'pending',
            // Store additional fields for invoice controllers
            'vat_number' => $request->organization_identifier, // VAT is same as organization_identifier
            'address' => $request->location_address, // Address for invoice generation
        ]);

        return redirect()->route('zatca.onboarding.show', $certificate)
                        ->with('success', 'Certificate created successfully. Now generate CSR.');
    }

    public function show(CertificateInfo $certificate)
    {
        return view('zatca.onboarding.show', compact('certificate'));
    }

    public function edit(CertificateInfo $certificate)
    {
        return view('zatca.onboarding.edit', compact('certificate'));
    }

    public function update(Request $request, CertificateInfo $certificate)
    {
        $request->validate([
            'organization_identifier' => 'required|string|max:255',
            'organization_name' => 'required|string|max:255',
            'organization_unit_name' => 'nullable|string|max:255',
            'common_name' => 'required|string|max:255',
            'country_name' => 'required|string|size:2',
            'location_address' => 'nullable|string|max:255',
            'business_category' => 'nullable|string|max:255',
            'environment_type' => 'required|in:NonProduction,Simulation,Production',
            'otp_used' => 'nullable|string|max:20',
        ]);

        // Reset certificate progress if environment changes
        $updateData = $request->only([
            'organization_identifier',
            'organization_name', 
            'organization_unit_name',
            'common_name',
            'country_name',
            'location_address',
            'business_category',
            'environment_type',
            'otp_used'
        ]);
        
        // Add additional fields for invoice controllers
        $updateData['vat_number'] = $request->organization_identifier;
        $updateData['address'] = $request->location_address;

        // If environment changed, reset all ZATCA-related fields
        if ($certificate->environment_type !== $request->environment_type) {
            $updateData = array_merge($updateData, [
                'csr' => null,
                'private_key' => null,
                'ccsid_requestID' => null,
                'ccsid_binarySecurityToken' => null,
                'ccsid_secret' => null,
                'pcsid_requestID' => null,
                'pcsid_binarySecurityToken' => null,
                'pcsid_secret' => null,
                'status' => 'pending',
                'lastICV' => 0,
                'lastInvoiceHash' => 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
            ]);

            // Update API URLs for new environment
            $apipath = match($request->environment_type) {
                'NonProduction' => 'developer-portal',
                'Simulation' => 'simulation', 
                'Production' => 'production',
                default => 'developer-portal'
            };

            $updateData = array_merge($updateData, [
                'complianceCsidUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/compliance",
                'complianceChecksUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/compliance/invoices",
                'productionCsidUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/production/csids",
                'reportingUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/invoices/reporting/single",
                'clearanceUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/invoices/clearance/single",
            ]);
        }

        $certificate->update($updateData);

        $message = $certificate->environment_type !== $request->environment_type 
            ? 'Certificate updated and reset for new environment. Please regenerate CSR.'
            : 'Certificate updated successfully.';

        return redirect()->route('zatca.onboarding.show', $certificate)
                        ->with('success', $message);
    }

    public function generateCSR(CertificateInfo $certificate)
    {
        try {
            // Build config from certificate data
            $config = [
                "csr.common.name" => $certificate->common_name,
                "csr.serial.number" => $certificate->serial_number,
                "csr.organization.identifier" => $certificate->organization_identifier,
                "csr.organization.unit.name" => $certificate->organization_unit_name,
                "csr.organization.name" => $certificate->organization_name,
                "csr.country.name" => $certificate->country_name,
                "csr.invoice.type" => $certificate->invoice_type ?: "1100",
                "csr.location.address" => $certificate->location_address,
                "csr.industry.business.category" => $certificate->business_category
            ];

            // Get API path based on environment
            $apipath = $this->getApiPath($certificate->environment_type);

            // Generate CSR
            $csrGen = new CsrGenerator($config, $certificate->environment_type);
            [$privateKeyContent, $csrBase64] = $csrGen->generateCsr();

            // Update certificate with CSR data and URLs
            $certificate->update([
                'csr' => $csrBase64,
                'private_key' => $privateKeyContent,
                'lastICV' => 0,
                'lastInvoiceHash' => "NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==",
                'complianceCsidUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/compliance",
                'complianceChecksUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/compliance/invoices",
                'productionCsidUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/production/csids",
                'reportingUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/invoices/reporting/single",
                'clearanceUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/invoices/clearance/single",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'CSR generated successfully',
                'csr' => $csrBase64
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate CSR: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getComplianceCSID(CertificateInfo $certificate)
    {
        try {
            if (!$certificate->csr) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSR not found. Generate CSR first.'
                ], 400);
            }

            // Prepare certificate info array for API call
            $certInfoArray = [
                'environment_type' => $certificate->environment_type,
                'csr' => $certificate->csr,
                'OTP' => $certificate->OTP ?: '123456',
                'complianceCsidUrl' => $certificate->complianceCsidUrl,
            ];

            $response = ApiHelper::complianceCSID($certInfoArray);
            $jsonDecodedResponse = json_decode($response, true);

            if ($jsonDecodedResponse && isset($jsonDecodedResponse['binarySecurityToken'])) {
                $certificate->update([
                    'ccsid_requestID' => $jsonDecodedResponse['requestID'] ?? '',
                    'ccsid_binarySecurityToken' => $jsonDecodedResponse['binarySecurityToken'],
                    'ccsid_secret' => $jsonDecodedResponse['secret'] ?? '',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Compliance CSID obtained successfully. Next: Run compliance checks.',
                    'data' => $jsonDecodedResponse
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get compliance CSID',
                'data' => $jsonDecodedResponse
            ], 400);

        } catch (Exception $e) {
            // Check if it's a connection issue
            if (strpos($e->getMessage(), 'Connection reset by peer') !== false || 
                strpos($e->getMessage(), 'cURL error') !== false) {
                
                // Check service status
                $serviceStatus = ApiHelper::checkServiceStatus($certificate->environment_type);
                
                return response()->json([
                    'success' => false,
                    'message' => 'ZATCA service connection failed. ' . $serviceStatus['message'] . '. Please try again in a few minutes.',
                    'error_type' => 'connection_error',
                    'service_status' => $serviceStatus,
                    'retry_suggested' => true
                ], 503);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting compliance CSID: ' . $e->getMessage(),
                'error_type' => 'api_error'
            ], 500);
        }
    }

    public function runComplianceChecks(CertificateInfo $certificate)
    {
        try {
            if (!$certificate->csid_certificate || !$certificate->csid_secret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compliance CSID not found. Get compliance CSID first.'
                ], 400);
            }

            // Load the invoice helper and signer
            $invoiceHelper = new InvoiceHelper();
            $signer = new EInvoiceSigner();

            // Define the sample documents to test
            $documentTypes = [
                ["STDSI", "388", "Standard Invoice", ""],
                ["STDCN", "383", "Standard CreditNote", "InstructionNotes for Standard CreditNote"],
                ["STDDN", "381", "Standard DebitNote", "InstructionNotes for Standard DebitNote"],
                ["SIMSI", "388", "Simplified Invoice", ""],
                ["SIMCN", "383", "Simplified CreditNote", "InstructionNotes for Simplified CreditNote"],
                ["SIMDN", "381", "Simplified DebitNote", "InstructionNotes for Simplified DebitNote"]
            ];

            $apiHelper = new ApiHelper();
            $apiPath = $this->getApiPath($certificate->environment_type);
            $url = "https://gw-fatoora.zatca.gov.sa/e-invoicing/{$apiPath}/compliance/invoices";

            $results = [];
            $icv = 0;
            $pih = "NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==";

            foreach ($documentTypes as $docType) {
                list($prefix, $typeCode, $description, $instructionNote) = $docType;
                $icv++;
                $isSimplified = strpos($prefix, "SIM") === 0;

                // Generate sample invoice XML
                $invoiceData = $this->generateSampleInvoice($prefix, $typeCode, $icv, $pih, $instructionNote, $isSimplified);
                $xml = $invoiceHelper->generateInvoiceXML($invoiceData);

                // Sign the XML
                $signedXml = $signer->signInvoice($xml, $certificate->private_key, $certificate->csid_certificate);
                $hash = base64_encode(hash('sha256', $signedXml, true));

                // Prepare API request
                $requestData = [
                    'invoiceHash' => $hash,
                    'invoice' => base64_encode($signedXml)
                ];

                // Send to ZATCA with Basic Auth using compliance CSID
                $response = $apiHelper->sendRequest($url, $requestData, [], 'POST', [
                    'Accept: application/json',
                    'Accept-Language: en',
                    'Content-Type: application/json',
                    'Accept-Version: V2',
                    'Authorization: Basic ' . base64_encode($certificate->csid_certificate . ':' . $certificate->csid_secret)
                ]);

                $status = $isSimplified 
                    ? ($response['reportingStatus'] ?? 'UNKNOWN')
                    : ($response['clearanceStatus'] ?? 'UNKNOWN');

                $results[] = [
                    'document' => $description,
                    'status' => $status,
                    'success' => (strpos($status, 'REPORTED') !== false || strpos($status, 'CLEARED') !== false)
                ];

                // Update PIH for next invoice
                if (isset($response['clearanceStatus']) || isset($response['reportingStatus'])) {
                    $pih = $hash;
                }

                // Small delay between requests
                usleep(200000); // 200ms
            }

            // Check if all passed
            $allPassed = collect($results)->every('success');

            if ($allPassed) {
                $certificate->update([
                    'compliance_checks_passed' => true,
                    'compliance_checks_results' => json_encode($results)
                ]);
            }

            return response()->json([
                'success' => $allPassed,
                'message' => $allPassed 
                    ? 'All compliance checks passed! Ready for production CSID.'
                    : 'Some compliance checks failed.',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error running compliance checks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProductionCSID(CertificateInfo $certificate)
    {
        try {
            if (!$certificate->ccsid_requestID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compliance CSID request ID not found. Get compliance CSID first.'
                ], 400);
            }

            // Prepare certificate info array for API call
            $certInfoArray = $certificate->toArray();

            $response = ApiHelper::productionCSID($certInfoArray);
            $jsonDecodedResponse = json_decode($response, true);

            if ($jsonDecodedResponse && isset($jsonDecodedResponse['binarySecurityToken'])) {
                $certificate->update([
                    'pcsid_requestID' => $jsonDecodedResponse['requestID'] ?? '',
                    'pcsid_binarySecurityToken' => $jsonDecodedResponse['binarySecurityToken'],
                    'pcsid_secret' => $jsonDecodedResponse['secret'] ?? '',
                    'status' => 'active',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Production CSID obtained successfully! Certificate is now ready for invoice submission.',
                    'data' => $jsonDecodedResponse
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get production CSID',
                'data' => $jsonDecodedResponse
            ], 400);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting production CSID: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(CertificateInfo $certificate)
    {
        $certificate->delete();
        return redirect()->route('zatca.onboarding.index')
                        ->with('success', 'Certificate deleted successfully.');
    }

    private function getApiPath($environmentType)
    {
        switch ($environmentType) {
            case 'Production':
                return 'production';
            case 'Simulation':
                return 'simulation';
            case 'NonProduction':
            default:
                return 'developer-portal';
        }
    }

    private function generateSampleInvoice($prefix, $typeCode, $icv, $pih, $instructionNote, $isSimplified)
    {
        return [
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'invoice_number' => $prefix . '-0001',
            'invoice_type' => $typeCode,
            'invoice_subtype' => $isSimplified ? '02' : '01',
            'issue_date' => now()->format('Y-m-d'),
            'issue_time' => now()->format('H:i:s'),
            'icv' => $icv,
            'previous_invoice_hash' => $pih,
            'seller' => [
                'name' => 'Maximum Speed Tech Supply LTD',
                'vat_number' => '399999999900003',
                'address' => 'Riyadh Branch',
            ],
            'buyer' => $isSimplified ? null : [
                'name' => 'Test Buyer',
                'vat_number' => '399999999900004',
                'address' => 'Test Address',
            ],
            'line_items' => [
                [
                    'name' => 'Sample Product',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'line_total' => 100.00,
                    'tax_rate' => 15,
                    'tax_amount' => 15.00,
                    'total_with_tax' => 115.00
                ]
            ],
            'subtotal' => 100.00,
            'tax_amount' => 15.00,
            'total_amount' => 115.00,
            'currency' => 'SAR',
            'instruction_note' => $instructionNote ?: null,
        ];
    }
}
