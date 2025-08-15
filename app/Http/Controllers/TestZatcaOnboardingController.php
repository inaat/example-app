<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Zatca\Helpers\CsrGenerator;
use App\Zatca\Helpers\ApiHelper;
use App\Zatca\Helpers\InvoiceHelper;
use App\Zatca\Helpers\EInvoiceSigner;
use App\Models\CertificateInfo; // Eloquent model replacing DbHelper
use DOMDocument;

class TestZatcaOnboardingController extends Controller
{
    public function onboard()
    {
        // ================= CONFIG ==================
        $config = [
            "csr.common.name" => "TST-886431145-399999999900003",
            "csr.serial.number" => "1-TST|2-TST|3-ed22f1d8-e6a2-1118-9b58-d9a8f11e445f",
            "csr.organization.identifier" => "399999999900003",
            "csr.organization.unit.name" => "Riyadh Branch",
            "csr.organization.name" => "Maximum Speed Tech Supply LTD",
            "csr.country.name" => "SA",
            "csr.invoice.type" => "1100",
            "csr.location.address" => "RRRD2929",
            "csr.industry.business.category" => "Supply activities"
        ];

        $environmentType = "NonProduction";
        $OTP = '123456'; // For simulation / production portal

        $apipath = match($environmentType) {
            'NonProduction' => 'developer-portal',
            'Simulation' => 'simulation',
            'Production' => 'production',
            default => 'developer-portal'
        };

        // Update company identifier for NonProduction
        if ($environmentType === "NonProduction") {
            $config["csr.organization.identifier"] = "399999999900003";
        }

       
        // ================= CERT INFO ==================
        $certInfo = [
            "environment_type" => $environmentType,
            "csr" => "",
            "private_key" => "",
            "OTP" => $OTP,
            "ccsid_requestID" => "",
            "ccsid_binarySecurityToken" => "",
            "ccsid_secret" => "",
            "pcsid_requestID" => "",
            "pcsid_binarySecurityToken" => "",
            "pcsid_secret" => "",
            "lastICV" => 0,
            "lastInvoiceHash" => "NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==",
            "complianceCsidUrl" => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/compliance",
            "complianceChecksUrl" => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/compliance/invoices",
            "productionCsidUrl" => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/production/csids",
            "reportingUrl" => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/invoices/reporting/single",
            "clearanceUrl" => "https://gw-fatoora.zatca.gov.sa/e-invoicing/$apipath/invoices/clearance/single",
        ];

        // ================= STEP 1: Generate CSR ==================
        $csrGen = new CsrGenerator($config, $environmentType);
        [$privateKeyContent, $csrBase64] = $csrGen->generateCsr();
        $certInfo["private_key"] = $privateKeyContent;
        $certInfo["csr"] = $csrBase64;

        // Save to DB
        CertificateInfo::updateOrCreate(
            ['environment_type' => $environmentType],
            $certInfo
        );

        // ================= STEP 2: Get Compliance CSID ==================
        $response = ApiHelper::complianceCSID($certInfo);
        $jsonDecodedResponse = json_decode($response, true);
        if ($jsonDecodedResponse) {
            $certInfo["ccsid_requestID"] = $jsonDecodedResponse["requestID"];
            $certInfo["ccsid_binarySecurityToken"] = $jsonDecodedResponse["binarySecurityToken"];
            $certInfo["ccsid_secret"] = $jsonDecodedResponse["secret"];
            CertificateInfo::updateOrCreate(
                ['environment_type' => $environmentType],
                $certInfo
            );
        }

        // ================= STEP 3: Send Sample Documents ==================
        $certInfo = CertificateInfo::where('environment_type', $environmentType)->first();
        $privateKey = $certInfo->private_key;
        $x509CertificateContent = base64_decode($certInfo->ccsid_binarySecurityToken);

        
      
        // ================= STEP 4: Get Production CSID ==================
        $response = ApiHelper::productionCSID($certInfo->toArray());
        $jsonDecodedResponse = json_decode($response, true);
        if ($jsonDecodedResponse) {
            $certInfo->pcsid_requestID = $jsonDecodedResponse["requestID"];
            $certInfo->pcsid_binarySecurityToken = $jsonDecodedResponse["binarySecurityToken"];
            $certInfo->pcsid_secret = $jsonDecodedResponse["secret"];
            $certInfo->save();
        }

        return response()->json([
            "message" => "Onboarding completed successfully",
            "certificate_info" => $certInfo
        ]);
    }
}
