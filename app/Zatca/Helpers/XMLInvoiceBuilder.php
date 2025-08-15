<?php
namespace App\Zatca\Helpers;
use DOMDocument;
use DOMXPath;
class XMLInvoiceBuilder {
    private $dom;
    private $invoice;
    private $xpath;
    
    public function __construct() {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
        $this->dom->preserveWhiteSpace = false;
        $this->initializeInvoice();
        $this->xpath = new DOMXPath($this->dom);
        $this->registerNamespaces();
    }
    
    private function initializeInvoice() {
        $this->invoice = $this->dom->createElementNS(
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 
            'Invoice'
        );
        $this->invoice->setAttributeNS(
            'http://www.w3.org/2000/xmlns/', 
            'xmlns:cac', 
            'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2'
        );
        $this->invoice->setAttributeNS(
            'http://www.w3.org/2000/xmlns/', 
            'xmlns:cbc', 
            'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'
        );
        $this->invoice->setAttributeNS(
            'http://www.w3.org/2000/xmlns/', 
            'xmlns:ext', 
            'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2'
        );
        $this->dom->appendChild($this->invoice);
        
        // Add UBL Extensions placeholder at the beginning
        $this->addUBLExtensionsPlaceholder();
    }
    
    private function registerNamespaces() {
        $this->xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $this->xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $this->xpath->registerNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $this->xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $this->xpath->registerNamespace('xades', 'http://uri.etsi.org/01903/v1.3.2#');
        $this->xpath->registerNamespace('sig', 'urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2');
        $this->xpath->registerNamespace('sac', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2');
        $this->xpath->registerNamespace('sbc', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2');
    }
    
    private function addUBLExtensionsPlaceholder() {
        // Create UBL Extensions element with all necessary namespaces
        $ublExtensions = $this->dom->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2', 'ext:UBLExtensions');
        
        $ublExtension = $this->dom->createElement('ext:UBLExtension');
        $extensionURI = $this->dom->createElement('ext:ExtensionURI', 'urn:oasis:names:specification:ubl:dsig:enveloped:xades');
        $extensionContent = $this->dom->createElement('ext:ExtensionContent');
        
        // Create the signature placeholder structure
        $ublDocumentSignatures = $this->dom->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2', 'sig:UBLDocumentSignatures');
        $ublDocumentSignatures->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sac', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2');
        $ublDocumentSignatures->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sbc', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2');
        
        $signatureInformation = $this->dom->createElement('sac:SignatureInformation');
        $signatureInformation->appendChild($this->dom->createElement('cbc:ID', 'urn:oasis:names:specification:ubl:signature:1'));
        $signatureInformation->appendChild($this->dom->createElement('sbc:ReferencedSignatureID', 'urn:oasis:names:specification:ubl:signature:Invoice'));
        
        // Create digital signature placeholder
        $dsSignature = $this->dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Signature');
        $dsSignature->setAttribute('Id', 'signature');
        
        // Add signature structure placeholders
        $this->addSignaturePlaceholderStructure($dsSignature);
        
        $signatureInformation->appendChild($dsSignature);
        $ublDocumentSignatures->appendChild($signatureInformation);
        $extensionContent->appendChild($ublDocumentSignatures);
        
        $ublExtension->appendChild($extensionURI);
        $ublExtension->appendChild($extensionContent);
        $ublExtensions->appendChild($ublExtension);
        
        // Insert at the beginning of the invoice
        $this->invoice->insertBefore($ublExtensions, $this->invoice->firstChild);
    }
    
    private function addSignaturePlaceholderStructure($dsSignature) {
        // SignedInfo
        $signedInfo = $this->dom->createElement('ds:SignedInfo');
        $signedInfo->appendChild($this->dom->createElement('ds:CanonicalizationMethod'))->setAttribute('Algorithm', 'http://www.w3.org/2006/12/xml-c14n11');
        $signedInfo->appendChild($this->dom->createElement('ds:SignatureMethod'))->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#ecdsa-sha256');
        
        // Invoice Reference
        $invoiceRef = $this->dom->createElement('ds:Reference');
        $invoiceRef->setAttribute('Id', 'invoiceSignedData');
        $invoiceRef->setAttribute('URI', '');
        
        $transforms = $this->dom->createElement('ds:Transforms');
        
        // XPath transforms
        $transform1 = $this->dom->createElement('ds:Transform');
        $transform1->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
        $transform1->appendChild($this->dom->createElement('ds:XPath', 'not(//ancestor-or-self::ext:UBLExtensions)'));
        $transforms->appendChild($transform1);
        
        $transform2 = $this->dom->createElement('ds:Transform');
        $transform2->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
        $transform2->appendChild($this->dom->createElement('ds:XPath', 'not(//ancestor-or-self::cac:Signature)'));
        $transforms->appendChild($transform2);
        
        $transform3 = $this->dom->createElement('ds:Transform');
        $transform3->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
        $transform3->appendChild($this->dom->createElement('ds:XPath', 'not(//ancestor-or-self::cac:AdditionalDocumentReference[cbc:ID=\'QR\'])'));
        $transforms->appendChild($transform3);
        
        $transform4 = $this->dom->createElement('ds:Transform');
        $transform4->setAttribute('Algorithm', 'http://www.w3.org/2006/12/xml-c14n11');
        $transforms->appendChild($transform4);
        
        $invoiceRef->appendChild($transforms);
        $invoiceRef->appendChild($this->dom->createElement('ds:DigestMethod'))->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $invoiceRef->appendChild($this->dom->createElement('ds:DigestValue', 'INVOICE_HASH'));
        
        // Properties Reference
        $propsRef = $this->dom->createElement('ds:Reference');
        $propsRef->setAttribute('Type', 'http://www.w3.org/2000/09/xmldsig#SignatureProperties');
        $propsRef->setAttribute('URI', '#xadesSignedProperties');
        $propsRef->appendChild($this->dom->createElement('ds:DigestMethod'))->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $propsRef->appendChild($this->dom->createElement('ds:DigestValue', 'SIGNED_PROPERTIES'));
        
        $signedInfo->appendChild($invoiceRef);
        $signedInfo->appendChild($propsRef);
        $dsSignature->appendChild($signedInfo);
        
        // Signature Value
        $dsSignature->appendChild($this->dom->createElement('ds:SignatureValue', 'SIGNATURE_VALUE'));
        
        // KeyInfo
        $keyInfo = $this->dom->createElement('ds:KeyInfo');
        $x509Data = $this->dom->createElement('ds:X509Data');
        $x509Data->appendChild($this->dom->createElement('ds:X509Certificate', 'CERTIFICATE_CONTENT'));
        $keyInfo->appendChild($x509Data);
        $dsSignature->appendChild($keyInfo);
        
        // XAdES Object
        $dsObject = $this->dom->createElement('ds:Object');
        $qualifyingProps = $this->dom->createElementNS('http://uri.etsi.org/01903/v1.3.2#', 'xades:QualifyingProperties');
        $qualifyingProps->setAttribute('Target', 'signature');
        
        $signedProps = $this->dom->createElement('xades:SignedProperties');
        $signedProps->setAttribute('Id', 'xadesSignedProperties');
        
        $signedSigProps = $this->dom->createElement('xades:SignedSignatureProperties');
        $signedSigProps->appendChild($this->dom->createElement('xades:SigningTime', 'SIGNATURE_TIMESTAMP'));
        
        $signingCert = $this->dom->createElement('xades:SigningCertificate');
        $cert = $this->dom->createElement('xades:Cert');
        
        $certDigest = $this->dom->createElement('xades:CertDigest');
        $certDigest->appendChild($this->dom->createElement('ds:DigestMethod'))->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $certDigest->appendChild($this->dom->createElement('ds:DigestValue', 'PUBLICKEY_HASHING'));
        
        $issuerSerial = $this->dom->createElement('xades:IssuerSerial');
        $issuerSerial->appendChild($this->dom->createElement('ds:X509IssuerName', 'ISSUER_NAME'));
        $issuerSerial->appendChild($this->dom->createElement('ds:X509SerialNumber', 'SERIAL_NUMBER'));
        
        $cert->appendChild($certDigest);
        $cert->appendChild($issuerSerial);
        $signingCert->appendChild($cert);
        $signedSigProps->appendChild($signingCert);
        $signedProps->appendChild($signedSigProps);
        $qualifyingProps->appendChild($signedProps);
        $dsObject->appendChild($qualifyingProps);
        $dsSignature->appendChild($dsObject);
    }
    
    public function setBasicInfo($profileId, $id, $uuid, $issueDate, $issueTime, $invoiceTypeCode, $invoiceTypeName, $documentCurrencyCode = 'SAR', $taxCurrencyCode = 'SAR') {
        $this->addElement('cbc:ProfileID', $profileId);
        $this->addElement('cbc:ID', $id);
        $this->addElement('cbc:UUID', $uuid ?: $this->generateUUIDv4());
        $this->addElement('cbc:IssueDate', $issueDate);
        $this->addElement('cbc:IssueTime', $issueTime);
        
        $typeCodeElement = $this->addElement('cbc:InvoiceTypeCode', $invoiceTypeCode);
        $typeCodeElement->setAttribute('name', $invoiceTypeName);
        
        return $this;
    }
    
    public function addNote($note, $languageId = 'ar') {
        $noteElement = $this->addElement('cbc:Note', $note);
        $noteElement->setAttribute('languageID', $languageId);
        return $this;
    }
    
    public function setCurrencyCodes($documentCurrencyCode = 'SAR', $taxCurrencyCode = 'SAR') {
        $this->addElement('cbc:DocumentCurrencyCode', $documentCurrencyCode);
        $this->addElement('cbc:TaxCurrencyCode', $taxCurrencyCode);
        return $this;
    }
    
    public function addBillingReference($invoiceId) {
        $billingRef = $this->addElement('cac:BillingReference');
        $docRef = $this->dom->createElement('cac:InvoiceDocumentReference');
        $docRef->appendChild($this->dom->createElement('cbc:ID', $invoiceId));
        $billingRef->appendChild($docRef);
        return $this;
    }
    
    public function addAdditionalDocumentReference($id, $uuid = null, $embeddedDoc = null, $mimeCode = 'text/plain') {
        $additionalDocRef = $this->addElement('cac:AdditionalDocumentReference');
        $additionalDocRef->appendChild($this->dom->createElement('cbc:ID', $id));
        
        if ($uuid !== null) {
            $additionalDocRef->appendChild($this->dom->createElement('cbc:UUID', $uuid));
        }
        
        if ($embeddedDoc !== null) {
            $attachment = $this->dom->createElement('cac:Attachment');
            $binaryObject = $this->dom->createElement('cbc:EmbeddedDocumentBinaryObject', $embeddedDoc);
            $binaryObject->setAttribute('mimeCode', $mimeCode);
            $attachment->appendChild($binaryObject);
            $additionalDocRef->appendChild($attachment);
        }
        
        return $this;
    }
    
    public function addQRCode($base64QRCode) {
        return $this->addAdditionalDocumentReference('QR', null, $base64QRCode, 'text/plain');
    }
    
    public function addSignature($signatureId = 'urn:oasis:names:specification:ubl:signature:Invoice', $signatureMethod = 'urn:oasis:names:specification:ubl:dsig:enveloped:xades') {
        $signature = $this->addElement('cac:Signature');
        $signature->appendChild($this->dom->createElement('cbc:ID', $signatureId));
        $signature->appendChild($this->dom->createElement('cbc:SignatureMethod', $signatureMethod));
        return $this;
    }
    
    public function setSupplierParty($companyId, $vatId, $registrationName, $address) {
        $supplierParty = $this->addElement('cac:AccountingSupplierParty');
        $party = $this->dom->createElement('cac:Party');
        
        if ($companyId) {
            $partyId = $this->dom->createElement('cac:PartyIdentification');
            $idElement = $this->dom->createElement('cbc:ID', $companyId);
            $idElement->setAttribute('schemeID', 'CRN');
            $partyId->appendChild($idElement);
            $party->appendChild($partyId);
        }
        
        $party->appendChild($this->buildAddress($address));
        $party->appendChild($this->buildTaxScheme($vatId));
        
        if ($registrationName) {
            $legalEntity = $this->dom->createElement('cac:PartyLegalEntity');
            $legalEntity->appendChild($this->dom->createElement('cbc:RegistrationName', $registrationName));
            $party->appendChild($legalEntity);
        }
        
        $supplierParty->appendChild($party);
        return $this;
    }
    
    public function setCustomerParty($companyId, $vatId, $registrationName, $address) {
        $customerParty = $this->addElement('cac:AccountingCustomerParty');
        $party = $this->dom->createElement('cac:Party');
        
        if ($companyId) {
            $partyId = $this->dom->createElement('cac:PartyIdentification');
            $idElement = $this->dom->createElement('cbc:ID', $companyId);
            $idElement->setAttribute('schemeID', 'CRN');
            $partyId->appendChild($idElement);
            $party->appendChild($partyId);
        }
        
        $party->appendChild($this->buildAddress($address));
        
        if ($vatId) {
            $party->appendChild($this->buildTaxScheme($vatId));
        }
        
        if ($registrationName) {
            $legalEntity = $this->dom->createElement('cac:PartyLegalEntity');
            $legalEntity->appendChild($this->dom->createElement('cbc:RegistrationName', $registrationName));
            $party->appendChild($legalEntity);
        }
        
        $customerParty->appendChild($party);
        return $this;
    }
    
    public function setDelivery($actualDeliveryDate) {
        $delivery = $this->addElement('cac:Delivery');
        $delivery->appendChild($this->dom->createElement('cbc:ActualDeliveryDate', $actualDeliveryDate));
        return $this;
    }
    
    public function setPaymentMeans($paymentMeansCode, $instructionNote = null) {
        $paymentMeans = $this->addElement('cac:PaymentMeans');
        $paymentMeans->appendChild($this->dom->createElement('cbc:PaymentMeansCode', $paymentMeansCode));
        
        if ($instructionNote) {
            $paymentMeans->appendChild($this->dom->createElement('cbc:InstructionNote', $instructionNote));
        }
        
        return $this;
    }
    
    public function addAllowanceCharge($isCharge, $reason, $amount, $currencyId = 'SAR', $taxCategoryId = 'S', $taxPercent = 15) {
        $allowanceCharge = $this->addElement('cac:AllowanceCharge');
        $allowanceCharge->appendChild($this->dom->createElement('cbc:ChargeIndicator', $isCharge ? 'true' : 'false'));
        $allowanceCharge->appendChild($this->dom->createElement('cbc:AllowanceChargeReason', $reason));
        
        $amountElement = $this->dom->createElement('cbc:Amount', $amount);
        $amountElement->setAttribute('currencyID', $currencyId);
        $allowanceCharge->appendChild($amountElement);
        
        $taxCategory = $this->dom->createElement('cac:TaxCategory');
        $taxIdElement = $this->dom->createElement('cbc:ID', $taxCategoryId);
        $taxIdElement->setAttribute('schemeID', 'UN/ECE 5305');
        $taxIdElement->setAttribute('schemeAgencyID', '6');
        $taxCategory->appendChild($taxIdElement);
        $taxCategory->appendChild($this->dom->createElement('cbc:Percent', $taxPercent));
        
        $taxScheme = $this->dom->createElement('cac:TaxScheme');
        $taxSchemeId = $this->dom->createElement('cbc:ID', 'VAT');
        $taxSchemeId->setAttribute('schemeID', 'UN/ECE 5153');
        $taxSchemeId->setAttribute('schemeAgencyID', '6');
        $taxScheme->appendChild($taxSchemeId);
        $taxCategory->appendChild($taxScheme);
        $allowanceCharge->appendChild($taxCategory);
        
        return $this;
    }
    
    public function addTaxTotal($taxAmount, $currencyId = 'SAR', $subtotals = []) {
        $taxTotal = $this->addElement('cac:TaxTotal');
        
        $taxAmountElement = $this->dom->createElement('cbc:TaxAmount', $taxAmount);
        $taxAmountElement->setAttribute('currencyID', $currencyId);
        $taxTotal->appendChild($taxAmountElement);
        
        foreach ($subtotals as $subtotal) {
            $taxSubtotal = $this->dom->createElement('cac:TaxSubtotal');
            
            $taxableAmountElement = $this->dom->createElement('cbc:TaxableAmount', $subtotal['taxableAmount']);
            $taxableAmountElement->setAttribute('currencyID', $currencyId);
            $taxSubtotal->appendChild($taxableAmountElement);
            
            $subtotalTaxAmountElement = $this->dom->createElement('cbc:TaxAmount', $subtotal['taxAmount']);
            $subtotalTaxAmountElement->setAttribute('currencyID', $currencyId);
            $taxSubtotal->appendChild($subtotalTaxAmountElement);
            
            $taxCategory = $this->dom->createElement('cac:TaxCategory');
            $taxIdElement = $this->dom->createElement('cbc:ID', $subtotal['categoryId']);
            $taxIdElement->setAttribute('schemeID', 'UN/ECE 5305');
            $taxIdElement->setAttribute('schemeAgencyID', '6');
            $taxCategory->appendChild($taxIdElement);
            $taxCategory->appendChild($this->dom->createElement('cbc:Percent', number_format($subtotal['percent'], 2)));
            
            $taxScheme = $this->dom->createElement('cac:TaxScheme');
            $taxSchemeId = $this->dom->createElement('cbc:ID', 'VAT');
            $taxSchemeId->setAttribute('schemeID', 'UN/ECE 5153');
            $taxSchemeId->setAttribute('schemeAgencyID', '6');
            $taxScheme->appendChild($taxSchemeId);
            $taxCategory->appendChild($taxScheme);
            $taxSubtotal->appendChild($taxCategory);
            
            $taxTotal->appendChild($taxSubtotal);
        }
        
        return $this;
    }
    
    public function setLegalMonetaryTotal($lineExtensionAmount, $taxExclusiveAmount, $taxInclusiveAmount, $allowanceTotalAmount = '0.00', $prepaidAmount = '0.00', $payableAmount = null, $currencyId = 'SAR') {
        $monetaryTotal = $this->addElement('cac:LegalMonetaryTotal');
        
        $lineExtElement = $this->dom->createElement('cbc:LineExtensionAmount', $lineExtensionAmount);
        $lineExtElement->setAttribute('currencyID', $currencyId);
        $monetaryTotal->appendChild($lineExtElement);
        
        $taxExcElement = $this->dom->createElement('cbc:TaxExclusiveAmount', $taxExclusiveAmount);
        $taxExcElement->setAttribute('currencyID', $currencyId);
        $monetaryTotal->appendChild($taxExcElement);
        
        $taxIncElement = $this->dom->createElement('cbc:TaxInclusiveAmount', $taxInclusiveAmount);
        $taxIncElement->setAttribute('currencyID', $currencyId);
        $monetaryTotal->appendChild($taxIncElement);
        
        $allowanceElement = $this->dom->createElement('cbc:AllowanceTotalAmount', $allowanceTotalAmount);
        $allowanceElement->setAttribute('currencyID', $currencyId);
        $monetaryTotal->appendChild($allowanceElement);
        
        $prepaidElement = $this->dom->createElement('cbc:PrepaidAmount', $prepaidAmount);
        $prepaidElement->setAttribute('currencyID', $currencyId);
        $monetaryTotal->appendChild($prepaidElement);
        
        $payableElement = $this->dom->createElement('cbc:PayableAmount', $payableAmount ?: $taxInclusiveAmount);
        $payableElement->setAttribute('currencyID', $currencyId);
        $monetaryTotal->appendChild($payableElement);
        
        return $this;
    }
    
    public function addInvoiceLine($lineId, $quantity, $unitCode, $lineExtensionAmount, $itemName, $priceAmount, $taxAmount = null, $roundingAmount = null, $taxCategoryId = 'S', $taxPercent = 15.00, $allowanceCharges = [], $currencyId = 'SAR') {
        $invoiceLine = $this->addElement('cac:InvoiceLine');
        
        $invoiceLine->appendChild($this->dom->createElement('cbc:ID', $lineId));
        
        $quantityElement = $this->dom->createElement('cbc:InvoicedQuantity', number_format($quantity, 6));
        $quantityElement->setAttribute('unitCode', $unitCode);
        $invoiceLine->appendChild($quantityElement);
        
        $lineExtElement = $this->dom->createElement('cbc:LineExtensionAmount', $lineExtensionAmount);
        $lineExtElement->setAttribute('currencyID', $currencyId);
        $invoiceLine->appendChild($lineExtElement);
        
        if ($taxAmount !== null) {
            $taxTotal = $this->dom->createElement('cac:TaxTotal');
            
            $taxAmountElement = $this->dom->createElement('cbc:TaxAmount', $taxAmount);
            $taxAmountElement->setAttribute('currencyID', $currencyId);
            $taxTotal->appendChild($taxAmountElement);
            
            if ($roundingAmount !== null) {
                $roundingElement = $this->dom->createElement('cbc:RoundingAmount', $roundingAmount);
                $roundingElement->setAttribute('currencyID', $currencyId);
                $taxTotal->appendChild($roundingElement);
            }
            
            $invoiceLine->appendChild($taxTotal);
        }
        
        $item = $this->dom->createElement('cac:Item');
        $item->appendChild($this->dom->createElement('cbc:Name', $itemName));
        
        $classifiedTaxCategory = $this->dom->createElement('cac:ClassifiedTaxCategory');
        $classifiedTaxCategory->appendChild($this->dom->createElement('cbc:ID', $taxCategoryId));
        $classifiedTaxCategory->appendChild($this->dom->createElement('cbc:Percent', number_format($taxPercent, 2)));
        
        $taxScheme = $this->dom->createElement('cac:TaxScheme');
        $taxScheme->appendChild($this->dom->createElement('cbc:ID', 'VAT'));
        $classifiedTaxCategory->appendChild($taxScheme);
        $item->appendChild($classifiedTaxCategory);
        $invoiceLine->appendChild($item);
        
        $price = $this->dom->createElement('cac:Price');
        $priceAmountElement = $this->dom->createElement('cbc:PriceAmount', $priceAmount);
        $priceAmountElement->setAttribute('currencyID', $currencyId);
        $price->appendChild($priceAmountElement);
        
        foreach ($allowanceCharges as $allowanceCharge) {
            $allowanceChargeElement = $this->dom->createElement('cac:AllowanceCharge');
            $allowanceChargeElement->appendChild($this->dom->createElement('cbc:ChargeIndicator', $allowanceCharge['isCharge'] ? 'true' : 'false'));
            $allowanceChargeElement->appendChild($this->dom->createElement('cbc:AllowanceChargeReason', $allowanceCharge['reason']));
            
            $chargeAmountElement = $this->dom->createElement('cbc:Amount', $allowanceCharge['amount']);
            $chargeAmountElement->setAttribute('currencyID', $currencyId);
            $allowanceChargeElement->appendChild($chargeAmountElement);
            
            $price->appendChild($allowanceChargeElement);
        }
        
        $invoiceLine->appendChild($price);
        
        return $this;
    }
    
    private function buildAddress($address) {
        $postalAddress = $this->dom->createElement('cac:PostalAddress');
        
        if (isset($address['streetName'])) {
            $postalAddress->appendChild($this->dom->createElement('cbc:StreetName', $address['streetName']));
        }
        if (isset($address['buildingNumber'])) {
            $postalAddress->appendChild($this->dom->createElement('cbc:BuildingNumber', $address['buildingNumber']));
        }
        if (isset($address['citySubdivisionName'])) {
            $postalAddress->appendChild($this->dom->createElement('cbc:CitySubdivisionName', $address['citySubdivisionName']));
        }
        if (isset($address['cityName'])) {
            $postalAddress->appendChild($this->dom->createElement('cbc:CityName', $address['cityName']));
        }
        if (isset($address['postalZone'])) {
            $postalAddress->appendChild($this->dom->createElement('cbc:PostalZone', $address['postalZone']));
        }
        
        $country = $this->dom->createElement('cac:Country');
        $country->appendChild($this->dom->createElement('cbc:IdentificationCode', $address['countryCode'] ?? 'SA'));
        $postalAddress->appendChild($country);
        
        return $postalAddress;
    }
    
    private function buildTaxScheme($vatId) {
        $partyTaxScheme = $this->dom->createElement('cac:PartyTaxScheme');
        $partyTaxScheme->appendChild($this->dom->createElement('cbc:CompanyID', $vatId));
        
        $taxScheme = $this->dom->createElement('cac:TaxScheme');
        $taxScheme->appendChild($this->dom->createElement('cbc:ID', 'VAT'));
        $partyTaxScheme->appendChild($taxScheme);
        
        return $partyTaxScheme;
    }
    
    private function addElement($tagName, $value = '') {
        $element = $this->dom->createElement($tagName, htmlspecialchars($value, ENT_XML1));
        $this->invoice->appendChild($element);
        return $element;
    }
    
    private function generateUUIDv4() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    public function getXML() {
        return $this->dom->saveXML();
    }
    
    public function getDOMDocument() {
        return $this->dom;
    }
    
    public function saveToFile($filename) {
        return $this->dom->save($filename);
    }
    
    public static function createFromTemplate($templatePath) {
        if (!file_exists($templatePath)) {
            throw new InvalidArgumentException("Template file not found: $templatePath");
        }
        
        $builder = new self();
        $builder->dom->load($templatePath);
        $builder->invoice = $builder->dom->documentElement;
        $builder->xpath = new DOMXPath($builder->dom);
        $builder->registerNamespaces();
        
        return $builder;
    }
}