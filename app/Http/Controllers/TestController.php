<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Zatca\Helpers\ApiHelper;
use App\Zatca\Helpers\InvoiceHelper;
use App\Zatca\Helpers\XMLInvoiceBuilder;
use Illuminate\Support\Facades\DB;
use App\Zatca\Signer\EInvoiceSigner;
use App\Models\CertificateInfo; // Eloquent model replacing DbHelper
use DOMDocument;
class TestController extends Controller
{
    // Simple test method
    public function index()
    {
          $certInfo = CertificateInfo::first();

        if (!$certInfo) {
            return response()->json([
                'error' => 'No certificate info found in database. Please run onboarding first.'
            ], 400);
        }
        $privateKey = $certInfo->private_key;
        $x509CertificateContent = base64_decode($certInfo->pcsid_binarySecurityToken);

        $documentTypes = [
            ["STDSI", "388", "Standard Invoice",""],
            ["STDCN", "383", "Standard CreditNote","InstructionNotes for Standard CreditNote"],
            ["STDDN", "381", "Standard DebitNote" , "InstructionNotes for Standard DebitNote"],
            ["SIMSI", "388", "Simplified Invoice",""],
            ["SIMCN", "383", "Simplified CreditNote", "InstructionNotes for Simplified CreditNote"],
            ["SIMDN", "381", "Simplified DebitNote", "InstructionNotes for Simplified DebitNote"]
        ];

        $icv = $certInfo->lastICV;
        $pih = $certInfo->lastInvoiceHash;
        $results = [];

        foreach ($documentTypes as $docType) {
            list($prefix, $typeCode, $description, $instructionNote) = $docType;
            $icv++;
            $isSimplified = strpos($prefix, "SIM") === 0;

            $builderDoc = $this->createInvoiceWithBuilder("{$prefix}-0001", $isSimplified, $typeCode, $icv, $pih, $instructionNote);

            $xmlString = $builderDoc->saveXML();
            $newDoc = new DOMDocument();
            $newDoc->preserveWhiteSpace = true;
            $newDoc->formatOutput = false;
            $newDoc->loadXML($xmlString);

            $jsonPayload = EInvoiceSigner::GetRequestApi($newDoc, $x509CertificateContent, $privateKey, true);

            if ($isSimplified) {
                $response = ApiHelper::invoiceReporting($certInfo->toArray(), $jsonPayload);
                $statusKey = "reportingStatus";
            } else {
                $response = ApiHelper::invoiceClearance($certInfo->toArray(), $jsonPayload);
                $statusKey = "clearanceStatus";
            }

            $jsonDecodedResponse = json_decode($response, true);
            if (!$jsonDecodedResponse) {
                return response()->json([
                    'error' => 'Invalid JSON response from server',
                    'server_response' => $response
                ], 500);
            }

            $status = $jsonDecodedResponse[$statusKey] ?? 'UNKNOWN';

            if (strpos($status, "REPORTED") !== false || strpos($status, "CLEARED") !== false) {
                $jsonPayloadDecoded = json_decode($jsonPayload, true);
                $certInfo->lastICV = $icv;

                if ($isSimplified) {
                    $pih = $jsonPayloadDecoded["invoiceHash"];
                    $certInfo->lastInvoiceHash = $pih;
                } else {
                    
                    list($invoiceHash, $base64QRCode) = InvoiceHelper::ExtractInvoiceHashAndBase64QrCode($jsonDecodedResponse["clearedInvoice"]);
                    $pih = $invoiceHash;
                    $certInfo->lastInvoiceHash = $pih;
                }

                // Save the updated model
                $certInfo->save();

                $results[] = [
                    'type' => $description,
                    'status' => 'success'
                ];
            } else {
                return response()->json([
                    'error' => "Failed to process {$description}",
                    'status' => $status
                ], 500);
            }

            usleep(200 * 1000); // 200ms delay
        }

        return response()->json([
            'message' => 'All invoice types processed successfully!',
            'results' => $results
        ]);
    }

    /**
     * Helper function to create invoice XML.
     */
    private function createInvoiceWithBuilder($invoiceId, $isSimplified, $typeCode, $icv, $pih, $instructionNote)
    {
        $builder = new XMLInvoiceBuilder();

        $builder->setBasicInfo(
            'reporting:1.0',
            $invoiceId,
            null,
            date('Y-m-d'),
            date('H:i:s'),
            $typeCode,
            $isSimplified ? "0200000" : "0100000"
        );

        $builder->addNote($instructionNote ?: 'ABC');
        $builder->setCurrencyCodes();
        $builder->addAdditionalDocumentReference('ICV', (string)$icv);
        $builder->addAdditionalDocumentReference('PIH', null, $pih);
        $builder->addQRCode('BASE64_QRCODE_PLACEHOLDER');
        $builder->addSignature();

        $supplierAddress = [
            'streetName' => 'الامير سلطان | Prince Sultan',
            'buildingNumber' => '2322',
            'citySubdivisionName' => 'المربع | Al-Murabba',
            'cityName' => 'الرياض | Riyadh',
            'postalZone' => '23333',
            'countryCode' => 'SA'
        ];

        $builder->setSupplierParty('1010010000', '399999999900003', 'Test Company LTD', $supplierAddress);

        $customerAddress = [
            'streetName' => 'صلاح الدين | Salah Al-Din',
            'buildingNumber' => '1111',
            'citySubdivisionName' => 'المروج | Al-Murooj',
            'cityName' => 'الرياض | Riyadh',
            'postalZone' => '12222',
            'countryCode' => 'SA'
        ];

        $builder->setCustomerParty(null, '399999999800003', 'Customer LTD', $customerAddress);
        $builder->setDelivery(date('Y-m-d'));
        $builder->setPaymentMeans('10', $instructionNote);
        $builder->addAllowanceCharge(false, 'discount', '0.00');

        $taxSubtotals = [
            [
                'taxableAmount' => '100.00',
                'taxAmount' => '15.00',
                'categoryId' => 'S',
                'percent' => 15.00
            ]
        ];

        $builder->addTaxTotal('15.00', 'SAR');
        $builder->addTaxTotal('15.00', 'SAR', $taxSubtotals);
        $builder->setLegalMonetaryTotal('100.00', '100.00', '115.00', '0.00', '0.00', '115.00');

        $builder->addInvoiceLine(
            '1',
            1.0,
            'PCE',
            '100.00',
            'Test Item',
            '100.00',
            '15.00',
            '115.00',
            'S',
            15.00,
            [['isCharge' => false, 'reason' => 'discount', 'amount' => '0.00']]
        );

        return $builder->getDOMDocument();
    }
}