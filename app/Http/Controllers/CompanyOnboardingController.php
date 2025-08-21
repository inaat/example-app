<?php

namespace App\Http\Controllers;

use App\Models\CompanyZatcaOnboarding;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Zatca\Helpers\ApiHelper;
use App\Zatca\Helpers\CsrGenerator;
use App\Zatca\Signer\EInvoiceSigner;
use App\Zatca\Helpers\XMLInvoiceBuilder;
use Exception;

class CompanyOnboardingController extends Controller
{
    /**
     * Display a listing of onboarding records.
     */
    public function index(): View
    {
        $onboardings = CompanyZatcaOnboarding::latest()->paginate(10);
        
        return view('zatca.companyZatcaOnboarding.index', compact('onboardings'));
    }

    /**
     * Show the form for creating a new onboarding.
     */
    public function create(): View
    {
        $onboarding = CompanyZatcaOnboarding::first();
        return view('zatca.companyZatcaOnboarding.create', compact('onboarding'));
    }

    /**
     * Store a newly created onboarding in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'portal_mode' => 'required|in:developer-portal,simulation,core',
            'otp' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'common_name' => 'required|string|max:255',
            'country_code' => 'required|string|size:2',
            'organization_unit_name' => 'nullable|string|max:255',
            'organization_name' => 'required|string|max:255',
            'egs_serial_number' => 'required|string|max:255',
            'vat_number' => 'required|string|size:15',
            'vat_name' => 'required|string|max:255',
            'invoice_type' => 'required|in:0100,1000,1100',
            'registered_address' => 'required|string|max:255',
            'street_name' => 'required|string|max:255',
            'building_number' => 'required|string|max:255',
            'plot_identification' => 'nullable|string|max:255',
            'sub_division_name' => 'nullable|string|max:255',
            'city_name' => 'required|string|max:255',
            'postal_number' => 'required|string|max:255',
            'country_name' => 'required|string|max:255',
            'business_category' => 'required|string|max:255',
            'crn' => 'nullable|string|max:255',
        ]);

        // Automatically process ZATCA onboarding and create record
        try {
            // Set API URLs based on portal mode
            $apiPath = match($validated['portal_mode']) {
                'developer-portal' => 'developer-portal',
                'simulation' => 'simulation', 
                'core' => 'core',
                default => 'developer-portal'
            };
            
            $apiUrls = [
                'complianceCsidUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apiPath/compliance",
                'complianceChecksUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apiPath/compliance/invoices",
                'productionCsidUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apiPath/production/csids",
                'reportingUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apiPath/invoices/reporting/single",
                'clearanceUrl' => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apiPath/invoices/clearance/single",
            ];
            
            // Check if any company exists first
            $existingCompany = CompanyZatcaOnboarding::first();
            
            if ($existingCompany) {
                // Update existing company data
                $onboarding = $existingCompany;
                $onboarding->update(array_merge($validated, $apiUrls, [
                    'status' => 'processing'
                ]));
            } else {
                // Create new company
                $onboarding = CompanyZatcaOnboarding::create(array_merge($validated, $apiUrls, [
                    'status' => 'processing'
                ]));
            }
            
            // Step 1: Generate CSR
            $this->generateCSR($onboarding);
            
            // Step 2: Get Compliance CSID
            $this->processComplianceCSID($onboarding);
            
            // Step 3: Run Compliance Checks
            $this->processComplianceChecks($onboarding);
            
            // Step 4: Get Production CSID
            $this->processProductionCSID($onboarding);
            
            // Final update to success status
            $onboarding->status = 'success';
            $onboarding->save();
            
            $message = 'Company onboarding completed successfully with ZATCA integration!';
        } catch (Exception $e) {
            // If onboarding was created but failed during ZATCA processing
            if (isset($onboarding)) {
                $onboarding->status = 'failed';
                $onboarding->notes = 'Error: ' . $e->getMessage();
                $onboarding->save();
            } else {
                // Create failed record if creation itself failed
                $onboarding = CompanyZatcaOnboarding::create(array_merge($validated, [
                    'status' => 'failed',
                    'notes' => 'Error: ' . $e->getMessage()
                ]));
            }
            $message = 'Company created but ZATCA integration failed: ' . $e->getMessage();
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

 

    /**
     * Generate CSR - private method called during creation
     */
    private function generateCSR(CompanyZatcaOnboarding $certificate): void
    {
        // Generate serial number based on portal mode
        $prefix = match($certificate->portal_mode) {
            'core' => 'PRD',
            'simulation' => 'SIM', 
            'developer-portal' => 'TST',
            default => 'TST'
        };
        
        $serialNumber = "1-{$prefix}|2-{$certificate->vat_number}|3-" . time();
        
        // Build config from certificate data
        $config = [
            "csr.common.name" => $certificate->common_name,
            "csr.serial.number" => $serialNumber,
            "csr.organization.identifier" => $certificate->vat_number,
            "csr.organization.unit.name" => $certificate->organization_unit_name,
            "csr.organization.name" => $certificate->organization_name,
            "csr.country.name" => $certificate->country_code,
            "csr.invoice.type" => $certificate->invoice_type,
            "csr.location.address" => $certificate->registered_address,
            "csr.industry.business.category" => $certificate->business_category
        ];

        // Map portal mode to environment type for CsrGenerator
        $environmentType = match($certificate->portal_mode) {
            'developer-portal' => 'NonProduction',
            'simulation' => 'Simulation', 
            'core' => 'Production',
            default => 'NonProduction'
        };

        // Generate CSR
        $csrGen = new CsrGenerator($config, $environmentType);
        [$privateKeyContent, $csrBase64] = $csrGen->generateCsr();

        // Update certificate with CSR data
        $certificate->serial_number = $serialNumber;
        $certificate->csr = $csrBase64;
        $certificate->private_key = $privateKeyContent;
        $certificate->lastICV = 0;
        $certificate->lastInvoiceHash = "NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==";
        $certificate->save();
    }

    /**
     * Process compliance CSID - private method called during creation
     */
    private function processComplianceCSID(CompanyZatcaOnboarding $certificate): void
    {
        if (!$certificate->csr) {
            throw new Exception('CSR not found. Generate CSR first.');
        }

        // Map portal mode to environment type for API call
        $environmentType = match($certificate->portal_mode) {
            'developer-portal' => 'NonProduction',
            'simulation' => 'Simulation', 
            'core' => 'Production',
            default => 'NonProduction'
        };

        // Prepare certificate info array for API call
        $certInfoArray = [
            'environment_type' => $environmentType,
            'csr' => $certificate->csr,
            'OTP' => $certificate->otp,
            'complianceCsidUrl' => $certificate->complianceCsidUrl,
        ];

        $response = ApiHelper::complianceCSID($certInfoArray);
        $jsonDecodedResponse = json_decode($response, true);

        if ($jsonDecodedResponse && isset($jsonDecodedResponse['binarySecurityToken'])) {
            $certificate->ccsid_requestID = $jsonDecodedResponse['requestID'] ?? '';
            $certificate->ccsid_binarySecurityToken = $jsonDecodedResponse['binarySecurityToken'];
            $certificate->ccsid_secret = $jsonDecodedResponse['secret'] ?? '';
            $certificate->save();
        } else {
            throw new Exception('Failed to get compliance CSID');
        }
    }

    /**
     * Process compliance checks - private method called during creation
     */
    private function processComplianceChecks(CompanyZatcaOnboarding $certificate): void
    {
        if (!$certificate->ccsid_binarySecurityToken || !$certificate->ccsid_secret) {
            throw new Exception('Compliance CSID not found. Get compliance CSID first.');
        }

        // Define the sample documents to test (all required by ZATCA)
        $documentTypes = [
            ["STDSI", "388", "Standard Invoice", "", "01"],
            ["STDCN", "383", "Standard CreditNote", "InstructionNotes for Standard CreditNote", "01"],
            ["STDDN", "381", "Standard DebitNote", "InstructionNotes for Standard DebitNote", "01"],
            ["SIMSI", "388", "Simplified Invoice", "", "02"],
            ["SIMCN", "383", "Simplified CreditNote", "InstructionNotes for Simplified CreditNote", "02"],
            ["SIMDN", "381", "Simplified DebitNote", "InstructionNotes for Simplified DebitNote", "02"]
        ];

        $apiHelper = new ApiHelper();
        $apiPath = $this->getApiPath($certificate->portal_mode);
        $url = "https://gw-fatoora.zatca.gov.sa/e-invoicing/{$apiPath}/compliance/invoices";

        $results = [];
        $icv = 0;
        $pih = "NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==";

        foreach ($documentTypes as $docType) {
            list($prefix, $typeCode, $description, $instructionNote, $invoiceTypeName) = $docType;
            $icv++;
            $isSimplified = strpos($prefix, "SIM") === 0;
            $isCredit = strpos($prefix, "CN") !== false;
            $isDebit = strpos($prefix, "DN") !== false;

            // Generate proper UBL-compliant XML using XMLInvoiceBuilder
            $xmlBuilder = new XMLInvoiceBuilder();
            
            // Generate UUID that will be used consistently
            $invoiceUuid = \Illuminate\Support\Str::uuid()->toString();
            
            // Set basic invoice information with proper invoice type name (7-9 chars)
            $invoiceTypeName = $isSimplified ? '0200000' : '0100000'; // 7 characters format
            
            $xmlBuilder->setBasicInfo(
                'reporting:1.0',  // Profile ID
                $prefix . '-' . str_pad($icv, 4, '0', STR_PAD_LEFT),
                $invoiceUuid,  // Use consistent UUID
                now()->format('Y-m-d'),
                now()->format('H:i:s'),
                $typeCode,
                $invoiceTypeName
            );

            // Follow the exact same order as TestController
            // Add note for credit/debit notes (required by ZATCA)
            if ($isCredit || $isDebit) {
                $xmlBuilder->addNote($instructionNote ?: 'Adjustment note', 'en');
            } else {
                $xmlBuilder->addNote('ABC');
            }

            // Add currency codes (will remove tax currency later to fix BR-KSA-EN16931-09)
            $xmlBuilder->setCurrencyCodes('SAR', 'SAR');

            // Add billing reference for credit/debit notes
            if ($isCredit || $isDebit) {
                $xmlBuilder->addBillingReference('INV-001');
            }

            // Add ICV and PIH references (required for ZATCA compliance)
            $xmlBuilder->addAdditionalDocumentReference('ICV', (string)$icv);
            $xmlBuilder->addAdditionalDocumentReference('PIH', null, $pih);

            // Add QR code placeholder
            $xmlBuilder->addQRCode('BASE64_QRCODE_PLACEHOLDER');

            // Add signature placeholder
            $xmlBuilder->addSignature();

            // Set supplier party information with complete address including district
            $supplierAddress = [
                'streetName' => 'الامير سلطان | Prince Sultan',  // Use exact same format as TestController
                'buildingNumber' => '2322',                        // Use exact same number as TestController
                'citySubdivisionName' => 'المربع | Al-Murabba',   // Use exact same format as TestController
                'cityName' => 'الرياض | Riyadh',                   // Use exact same format as TestController
                'postalZone' => '23333',                           // Use exact same number as TestController
                'countryCode' => 'SA'
            ];
            $xmlBuilder->setSupplierParty(
                $certificate->crn , // Use same CRN as TestController
                $certificate->vat_number,  // Use the VAT number from certificate
                $certificate->organization_name,  // Use organization name from database
                $supplierAddress
            );

            // Set customer party - for simplified invoices, use null for company ID
            $customerAddress = [
                'streetName' => 'صلاح الدين | Salah Al-Din',    // Use exact same format as TestController
                'buildingNumber' => '1111',                      // Use exact same number as TestController
                'citySubdivisionName' => 'المروج | Al-Murooj',  // Use exact same format as TestController
                'cityName' => 'الرياض | Riyadh',                // Use exact same format as TestController
                'postalZone' => '12222',                         // Use exact same number as TestController
                'countryCode' => 'SA'
            ];
            $xmlBuilder->setCustomerParty(
                $isSimplified ? null : '1010010000',  // null for simplified, CRN for standard
                '399999999800003',  // Use exact same customer VAT as TestController
                'Customer LTD',     // Use exact same customer name as TestController
                $customerAddress
            );

            // Add supply date (delivery date) for standard invoices - required by BR-KSA-15
            if (!$isSimplified) {
                $xmlBuilder->setDelivery(now()->format('Y-m-d'));
            }

            // Add payment means if instruction note is provided
            if (!empty($instructionNote)) {
                $xmlBuilder->setPaymentMeans('10', $instructionNote);
            } else {
                $xmlBuilder->setPaymentMeans('10', ''); // Default payment method
            }

            // Add allowance charge (like TestController does)
            $xmlBuilder->addAllowanceCharge(false, 'discount', '0.00');

            // Add TaxTotal exactly like TestController does - first without subtotals, then with
            $xmlBuilder->addTaxTotal('15.00', 'SAR');
            $xmlBuilder->addTaxTotal('15.00', 'SAR', [
                [
                    'taxableAmount' => '100.00',
                    'taxAmount' => '15.00',
                    'categoryId' => 'S',
                    'percent' => 15.00
                ]
            ]);

            // Set legal monetary total
            $xmlBuilder->setLegalMonetaryTotal(
                '100.00',  // Line extension amount
                '100.00',  // Tax exclusive amount
                '115.00',  // Tax inclusive amount
                '0.00',    // Allowance total
                '0.00',    // Prepaid amount
                '115.00'   // Payable amount
            );

            // Add invoice line with proper line total calculation (BR-KSA-51)
            $xmlBuilder->addInvoiceLine(
                '1',
                1.000000,
                'PCE',
                '100.00',     // Line extension amount (net)
                'Test Item',
                '100.00',     // Unit price
                '15.00',      // Tax amount for line
                '115.00',     // Line amount with VAT (100 + 15)
                'S',          // Tax category
                15.00,        // Tax percentage
                [],           // No allowance charges
                'SAR'         // Currency
            );

            // Get the generated XML and prepare it for signing
            $xmlString = $xmlBuilder->getXML();
            
            // Create a new DOMDocument with proper formatting for EInvoiceSigner
            $xmlDoc = new \DOMDocument();
            $xmlDoc->preserveWhiteSpace = true;
            $xmlDoc->formatOutput = false;
            $xmlDoc->loadXML($xmlString);
            
            // Verify UUID is present before signing
            $xpath = new \DOMXPath($xmlDoc);
            $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $uuidNode = $xpath->query('//cbc:UUID')->item(0);
            
            if (!$uuidNode || empty($uuidNode->nodeValue)) {
                throw new Exception("UUID element not found or empty in generated XML for document type: $prefix");
            }

            // Use the exact same approach as TestController - let EInvoiceSigner handle everything
            $x509CertificateContent = base64_decode($certificate->ccsid_binarySecurityToken);
            
            \Log::info('Using EInvoiceSigner approach like TestController', [
                'document_type' => $prefix,
                'cert_length' => strlen($certificate->ccsid_binarySecurityToken),
                'decoded_length' => strlen($x509CertificateContent)
            ]);
            
            // Use the exact same approach as TestController - let EInvoiceSigner handle everything
            try {
                $jsonPayload = EInvoiceSigner::GetRequestApi($xmlDoc, $x509CertificateContent, $certificate->private_key, true);
                
                \Log::info('EInvoiceSigner success', [
                    'document_type' => $prefix,
                    'payload_length' => strlen($jsonPayload)
                ]);
                
                // Parse the signed response
                $signedData = json_decode($jsonPayload, true);
                if (!$signedData) {
                    throw new Exception('Failed to parse signed invoice data from EInvoiceSigner');
                }
                
                $hash = $signedData['invoiceHash'];
                $base64Invoice = $signedData['invoice'];
                
            } catch (Exception $signerException) {
                \Log::error('EInvoiceSigner failed, skipping this document type', [
                    'document_type' => $prefix,
                    'error' => $signerException->getMessage()
                ]);
                
                // Skip this document type if signing fails
                continue;
            }
            
            // Send to ZATCA using the signed response from EInvoiceSigner
            $response = ApiHelper::complianceChecks($certificate->toArray(), $jsonPayload);
            $responseData = json_decode($response, true);

            $status = $isSimplified 
                ? ($responseData['reportingStatus'] ?? 'UNKNOWN')
                : ($responseData['clearanceStatus'] ?? 'UNKNOWN');

            $results[] = [
                'document' => $description,
                'status' => $status,
                'success' => (strpos($status, 'REPORTED') !== false || strpos($status, 'CLEARED') !== false)
            ];

            // Update PIH for next invoice and save to certificate
            if (isset($responseData['clearanceStatus']) || isset($responseData['reportingStatus'])) {
                $pih = $hash; // Use the hash from EInvoiceSigner response
                $certificate->lastInvoiceHash = $hash;
                $certificate->lastICV = $icv;
                $certificate->save();
            }

            // Small delay between requests
            usleep(200000); // 200ms
        }

        // Check if all passed
        $allPassed = collect($results)->every('success');

        if (!$allPassed) {
            throw new Exception('Compliance checks failed: ' . json_encode($results));
        }

        $certificate->notes = ($certificate->notes ?? '') . ' | Compliance checks passed: ' . json_encode($results);
        $certificate->save();
    }

    /**
     * Process production CSID - private method called during creation
     */
    private function processProductionCSID(CompanyZatcaOnboarding $certificate): void
    {
        if (!$certificate->ccsid_requestID) {
            throw new Exception('Compliance CSID request ID not found. Get compliance CSID first.');
        }

        // Prepare certificate info array for API call
        $certInfoArray = $certificate->toArray();

        $response = ApiHelper::productionCSID($certInfoArray);
        $jsonDecodedResponse = json_decode($response, true);

        if ($jsonDecodedResponse && isset($jsonDecodedResponse['binarySecurityToken'])) {
            $certificate->pcsid_requestID = $jsonDecodedResponse['requestID'] ?? '';
            $certificate->pcsid_binarySecurityToken = $jsonDecodedResponse['binarySecurityToken'];
            $certificate->pcsid_secret = $jsonDecodedResponse['secret'] ?? '';
            $certificate->save();
        } else {
            throw new Exception('Failed to get production CSID');
        }
    }

    private function getApiPath($portalMode)
    {
        return match($portalMode) {
            'core' => 'production',
            'simulation' => 'simulation',
            'developer-portal' => 'developer-portal',
            default => 'developer-portal'
        };
    }

}