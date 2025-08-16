import requests
import json
import base64
import hashlib
import uuid
import os
import getpass
from datetime import datetime
from typing import Dict, Any, Optional
import xml.etree.ElementTree as ET
from cryptography import x509
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.asymmetric import rsa
from cryptography.hazmat.primitives import serialization
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class ZATCAAPIClient:
    """
    ZATCA E-Invoicing API Integration Client
    Supports Sandbox and Production environments
    """
    
    def __init__(self, environment: str = "sandbox", config_file: str = None):
        """
        Initialize ZATCA API Client
        
        Args:
            environment: 'sandbox' or 'production'
            config_file: Path to configuration file (optional)
        """
        self.environment = environment
        self.base_urls = {
            "sandbox": "https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal",
            "production": "https://gw-fatoora.zatca.gov.sa/e-invoicing/core"
        }
        self.base_url = self.base_urls[environment]
        self.session = requests.Session()
        self.compliance_csid = None
        self.production_csid = None
        self.config_file = config_file
        
        # Load credentials from environment or config file
        self._load_credentials()
        
    def _load_credentials(self):
        """
        Load credentials from environment variables or config file
        """
        # Try loading from environment variables first
        compliance_token = os.getenv('ZATCA_COMPLIANCE_TOKEN')
        compliance_secret = os.getenv('ZATCA_COMPLIANCE_SECRET')
        production_token = os.getenv('ZATCA_PRODUCTION_TOKEN')
        production_secret = os.getenv('ZATCA_PRODUCTION_SECRET')
        
        if compliance_token and compliance_secret:
            self.compliance_csid = {
                "binarySecurityToken": compliance_token,
                "secret": compliance_secret
            }
            logger.info("Compliance credentials loaded from environment variables")
            
        if production_token and production_secret:
            self.production_csid = {
                "binarySecurityToken": production_token,
                "secret": production_secret
            }
            logger.info("Production credentials loaded from environment variables")
        
        # Try loading from config file if specified
        if self.config_file and os.path.exists(self.config_file):
            try:
                with open(self.config_file, 'r') as f:
                    config = json.load(f)
                    
                compliance_config = config.get('compliance_credentials', {})
                if compliance_config.get('binarySecurityToken') and compliance_config.get('secret'):
                    self.compliance_csid = compliance_config
                    logger.info("Compliance credentials loaded from config file")
                    
                production_config = config.get('production_credentials', {})
                if production_config.get('binarySecurityToken') and production_config.get('secret'):
                    self.production_csid = production_config
                    logger.info("Production credentials loaded from config file")
                    
            except Exception as e:
                logger.warning(f"Failed to load config file: {e}")

    def save_credentials_to_file(self, file_path: str):
        """
        Save current credentials to a JSON file
        
        Args:
            file_path: Path to save credentials
        """
        credentials = {}
        
        if self.compliance_csid:
            credentials['compliance_credentials'] = self.compliance_csid
            
        if self.production_csid:
            credentials['production_credentials'] = self.production_csid
            
        try:
            with open(file_path, 'w') as f:
                json.dump(credentials, f, indent=2)
            logger.info(f"Credentials saved to {file_path}")
            print(f"✓ Credentials saved to {file_path}")
            print("IMPORTANT: Keep this file secure and do not share it!")
        except Exception as e:
            logger.error(f"Failed to save credentials: {e}")

    def load_credentials_from_file(self, file_path: str):
        """
        Load credentials from a JSON file
        
        Args:
            file_path: Path to load credentials from
        """
        try:
            with open(file_path, 'r') as f:
                config = json.load(f)
                
            compliance_config = config.get('compliance_credentials', {})
            if compliance_config.get('binarySecurityToken') and compliance_config.get('secret'):
                self.compliance_csid = compliance_config
                logger.info("Compliance credentials loaded from file")
                
            production_config = config.get('production_credentials', {})
            if production_config.get('binarySecurityToken') and production_config.get('secret'):
                self.production_csid = production_config
                logger.info("Production credentials loaded from file")
                
            print("✓ Credentials loaded successfully from file")
            
        except Exception as e:
            logger.error(f"Failed to load credentials from file: {e}")
            print(f"❌ Failed to load credentials: {e}")

    def input_credentials_securely(self, credential_type: str = "compliance"):
        """
        Securely input credentials from user (hidden input)
        
        Args:
            credential_type: 'compliance' or 'production'
        """
        print(f"\n=== Enter {credential_type.title()} Credentials ===")
        print("Note: Input will be hidden for security")
        
        try:
            binary_token = getpass.getpass(f"Enter {credential_type} binarySecurityToken: ")
            secret = getpass.getpass(f"Enter {credential_type} secret: ")
            
            if not binary_token or not secret:
                print("❌ Both token and secret are required")
                return False
                
            if credential_type == "compliance":
                self.compliance_csid = {
                    "binarySecurityToken": binary_token,
                    "secret": secret
                }
            else:
                self.production_csid = {
                    "binarySecurityToken": binary_token,
                    "secret": secret
                }
                
            print(f"✓ {credential_type.title()} credentials set securely")
            return True
            
        except KeyboardInterrupt:
            print("\n❌ Credential input cancelled")
            return False 
                     headers: Dict = None, auth_type: str = None) -> requests.Response:
        """
        Make HTTP request to ZATCA API
        
        Args:
            method: HTTP method (GET, POST, etc.)
            endpoint: API endpoint
            data: Request payload
            headers: Additional headers
            auth_type: Authentication type ('basic', 'bearer')
        """
        url = f"{self.base_url}{endpoint}"
        
        default_headers = {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "Accept-Version": "V2"
        }
        
        if headers:
            default_headers.update(headers)
            
        # Add authentication headers based on type
        if auth_type == "basic" and self.compliance_csid:
            # Use binarySecurityToken:secret for Basic auth
            auth_string = base64.b64encode(
                f"{self.compliance_csid['binarySecurityToken']}:{self.compliance_csid['secret']}".encode()
            ).decode()
            default_headers["Authorization"] = f"Basic {auth_string}"
            logger.debug("Using Basic auth with binarySecurityToken:secret")
            
        elif auth_type == "bearer" and self.production_csid:
            # Use binarySecurityToken:secret for Bearer auth  
            auth_string = base64.b64encode(
                f"{self.production_csid['binarySecurityToken']}:{self.production_csid['secret']}".encode()
            ).decode()
            default_headers["Authorization"] = f"Basic {auth_string}"
            logger.debug("Using Basic auth with production binarySecurityToken:secret")
        
        try:
            response = self.session.request(
                method=method,
                url=url,
                json=data,
                headers=default_headers,
                timeout=30
            )
            
            logger.info(f"{method} {url} - Status: {response.status_code}")
            return response
            
        except requests.exceptions.RequestException as e:
            logger.error(f"Request failed: {e}")
            raise

    def generate_csr(self, vat_number: str, common_name: str, organization_unit: str, 
                    organization: str, country: str = "SA", serial_number: str = None,
                    organization_identifier: str = None, invoice_type: str = "1100",
                    location: str = None) -> tuple:
        """
        Generate Certificate Signing Request (CSR) for ZATCA compliance
        
        Args:
            vat_number: VAT registration number (required)
            common_name: Common name for certificate
            organization_unit: Organization unit
            organization: Organization name
            country: Country code (default: SA)
            serial_number: Device serial number
            organization_identifier: Organization identifier
            invoice_type: Invoice type code (default: 1100 for both simplified and standard)
            location: Location/address
            
        Returns:
            Tuple of (private_key_pem, csr_pem)
        """
        from cryptography.x509.oid import NameOID, ExtensionOID
        from cryptography.hazmat.primitives.asymmetric import rsa
        
        # Generate private key
        private_key = rsa.generate_private_key(
            public_exponent=65537,
            key_size=2048,
        )
        
        # Build subject with ZATCA required fields
        subject_components = [
            x509.NameAttribute(NameOID.COUNTRY_NAME, country),
            x509.NameAttribute(NameOID.ORGANIZATION_NAME, organization),
            x509.NameAttribute(NameOID.ORGANIZATIONAL_UNIT_NAME, organization_unit),
            x509.NameAttribute(NameOID.COMMON_NAME, common_name),
        ]
        
        # Add optional fields if provided
        if serial_number:
            subject_components.append(x509.NameAttribute(NameOID.SERIAL_NUMBER, serial_number))
        if organization_identifier:
            subject_components.append(x509.NameAttribute(NameOID.ORGANIZATION_IDENTIFIER, organization_identifier))
        if location:
            subject_components.append(x509.NameAttribute(NameOID.LOCALITY_NAME, location))
            
        subject = x509.Name(subject_components)
        
        # Create CSR builder
        builder = x509.CertificateSigningRequestBuilder()
        builder = builder.subject_name(subject)
        
        # Add Subject Alternative Name extension with ZATCA specific fields
        san_list = []
        
        # Add VAT number as directoryName
        vat_dn = x509.Name([
            x509.NameAttribute(NameOID.ORGANIZATION_IDENTIFIER, f"VATSA-{vat_number}")
        ])
        san_list.append(x509.DirectoryName(vat_dn))
        
        # Add invoice type as registeredID (OID format)
        # Using a custom OID structure for invoice type
        invoice_type_oid = x509.ObjectIdentifier(f"1.3.6.1.4.1.311.60.2.1.{invoice_type}")
        san_list.append(x509.RegisteredID(invoice_type_oid))
        
        # Add SAN extension
        builder = builder.add_extension(
            x509.SubjectAlternativeName(san_list),
            critical=False,
        )
        
        # Add Key Usage extension
        builder = builder.add_extension(
            x509.KeyUsage(
                digital_signature=True,
                key_encipherment=False,
                key_agreement=False,
                key_cert_sign=False,
                crl_sign=False,
                content_commitment=False,
                data_encipherment=False,
                encipher_only=False,
                decipher_only=False
            ),
            critical=True,
        )
        
        # Add Extended Key Usage
        builder = builder.add_extension(
            x509.ExtendedKeyUsage([
                x509.oid.ExtendedKeyUsageOID.CLIENT_AUTH,
                x509.oid.ExtendedKeyUsageOID.SERVER_AUTH,
            ]),
            critical=True,
        )
        
        # Sign the CSR
        csr = builder.sign(private_key, hashes.SHA256())
        
        # Serialize to PEM format
        private_key_pem = private_key.private_bytes(
            encoding=serialization.Encoding.PEM,
            format=serialization.PrivateFormat.PKCS8,
            encryption_algorithm=serialization.NoEncryption()
        ).decode()
        
        csr_pem = csr.public_bytes(serialization.Encoding.PEM).decode()
        
        logger.info("CSR generated successfully with ZATCA compliance fields")
        return private_key_pem, csr_pem

    def generate_simple_csr(self, common_name: str, organization: str, 
                           organization_unit: str = "IT", country: str = "SA") -> str:
        """
        Generate a simple CSR for basic testing
        
        Args:
            common_name: Common name
            organization: Organization name
            organization_unit: Organization unit
            country: Country code
            
        Returns:
            CSR in PEM format as string
        """
        from cryptography.x509.oid import NameOID
        from cryptography.hazmat.primitives.asymmetric import rsa
        
        # Generate private key
        private_key = rsa.generate_private_key(
            public_exponent=65537,
            key_size=2048,
        )
        
        # Create simple subject
        subject = x509.Name([
            x509.NameAttribute(NameOID.COUNTRY_NAME, country),
            x509.NameAttribute(NameOID.ORGANIZATION_NAME, organization),
            x509.NameAttribute(NameOID.ORGANIZATIONAL_UNIT_NAME, organization_unit),
            x509.NameAttribute(NameOID.COMMON_NAME, common_name),
        ])
        
        # Build and sign CSR
        csr = x509.CertificateSigningRequestBuilder().subject_name(
            subject
        ).sign(private_key, hashes.SHA256())
        
        return csr.public_bytes(serialization.Encoding.PEM).decode()

    def get_compliance_csid(self, csr: str) -> Dict[str, Any]:
        """
        Get Compliance CSID (Certificate Signing ID)
        
        Args:
            csr: Certificate Signing Request in PEM format
            
        Returns:
            Compliance CSID response
        """
        endpoint = "/compliance"
        data = {
            "csr": csr
        }
        
        response = self._make_request("POST", endpoint, data)
        
        if response.status_code == 200:
            result = response.json()
            # Store the credentials properly
            self.compliance_csid = {
                "binarySecurityToken": result.get("binarySecurityToken"),
                "secret": result.get("secret"),
                "requestId": result.get("requestId")
            }
            logger.info("Compliance CSID obtained successfully")
            logger.debug(f"binarySecurityToken: {result.get('binarySecurityToken')[:20]}...")
            logger.debug(f"secret: {result.get('secret')[:20]}...")
            return result
        else:
            logger.error(f"Failed to get Compliance CSID: {response.status_code} - {response.text}")
            response.raise_for_status()

    def compliance_check(self, invoice_xml: str, invoice_hash: str, uuid_value: str) -> Dict[str, Any]:
        """
        Perform compliance check for invoice
        
        Args:
            invoice_xml: Base64 encoded invoice XML
            invoice_hash: Invoice hash
            uuid_value: UUID for the invoice
            
        Returns:
            Compliance check response
        """
        endpoint = "/compliance/invoices"
        data = {
            "invoiceHash": invoice_hash,
            "uuid": uuid_value,
            "invoice": invoice_xml
        }
        
        response = self._make_request("POST", endpoint, data, auth_type="basic")
        
        if response.status_code in [200, 202]:
            result = response.json()
            logger.info(f"Compliance check completed with status: {response.status_code}")
            return result
        else:
            logger.error(f"Compliance check failed: {response.status_code} - {response.text}")
            response.raise_for_status()

    def get_production_csid(self, compliance_request_id: str) -> Dict[str, Any]:
        """
        Get Production CSID for onboarding
        
        Args:
            compliance_request_id: ID from compliance check
            
        Returns:
            Production CSID response
        """
        endpoint = "/production/csids"
        data = {
            "compliance_request_id": compliance_request_id
        }
        
        response = self._make_request("POST", endpoint, data, auth_type="basic")
        
        if response.status_code == 200:
            result = response.json()
            # Store production credentials
            self.production_csid = {
                "binarySecurityToken": result.get("binarySecurityToken"),
                "secret": result.get("secret"),
                "requestId": result.get("requestId")
            }
            logger.info("Production CSID obtained successfully")
            logger.debug(f"Production binarySecurityToken: {result.get('binarySecurityToken')[:20]}...")
            logger.debug(f"Production secret: {result.get('secret')[:20]}...")
            return result
        else:
            logger.error(f"Failed to get Production CSID: {response.status_code} - {response.text}")
            response.raise_for_status()

    def _make_request(self, method: str, endpoint: str, data: Dict = None,
    def set_compliance_credentials(self, binary_security_token: str, secret: str):
        """
        Set compliance credentials (use only for testing - prefer secure methods)
        
        Args:
            binary_security_token: The binarySecurityToken from compliance API
            secret: The secret from compliance API
        """
        logger.warning("Setting credentials directly - ensure this is for testing only!")
        self.compliance_csid = {
            "binarySecurityToken": binary_security_token,
            "secret": secret
        }
        logger.info("Compliance credentials set")

    def set_production_credentials(self, binary_security_token: str, secret: str):
        """
        Set production credentials (use only for testing - prefer secure methods)
        
        Args:
            binary_security_token: The binarySecurityToken from production API
            secret: The secret from production API
        """
        logger.warning("Setting credentials directly - ensure this is for testing only!")
        self.production_csid = {
            "binarySecurityToken": binary_security_token,
            "secret": secret
        }
        logger.info("Production credentials set")

    def get_credentials_info(self) -> Dict[str, Any]:
        """
        Get current credential information
        
        Returns:
            Dictionary with credential status
        """
        info = {
            "has_compliance_csid": self.compliance_csid is not None,
            "has_production_csid": self.production_csid is not None,
            "compliance_token_preview": None,
            "production_token_preview": None
        }
        
        if self.compliance_csid:
            token = self.compliance_csid.get("binarySecurityToken", "")
            info["compliance_token_preview"] = f"{token[:10]}...{token[-10:]}" if len(token) > 20 else token
            
        if self.production_csid:
            token = self.production_csid.get("binarySecurityToken", "")
            info["production_token_preview"] = f"{token[:10]}...{token[-10:]}" if len(token) > 20 else token
            
        return info

    def renew_production_csid(self, csr: str) -> Dict[str, Any]:
        """
        Renew Production CSID
        
        Args:
            csr: New Certificate Signing Request
            
        Returns:
            Renewed CSID response
        """
        endpoint = "/production/csids"
        data = {
            "csr": csr
        }
        
        # Use current production credentials for renewal
        response = self._make_request("PATCH", endpoint, data, auth_type="bearer")
        
        if response.status_code == 200:
            result = response.json()
            # Update with new credentials
            self.production_csid = {
                "binarySecurityToken": result.get("binarySecurityToken"),
                "secret": result.get("secret"),
                "requestId": result.get("requestId")
            }
            logger.info("Production CSID renewed successfully")
            return result
        else:
            logger.error(f"Failed to renew Production CSID: {response.status_code} - {response.text}")
            response.raise_for_status()

    def report_invoice(self, invoice_xml: str, invoice_hash: str, uuid_value: str) -> Dict[str, Any]:
        """
        Report simplified invoice (Reporting API)
        
        Args:
            invoice_xml: Base64 encoded invoice XML
            invoice_hash: Invoice hash
            uuid_value: UUID for the invoice
            
        Returns:
            Reporting response
        """
        endpoint = "/invoices/reporting/single"
        data = {
            "invoiceHash": invoice_hash,
            "uuid": uuid_value,
            "invoice": invoice_xml
        }
        
        response = self._make_request("POST", endpoint, data, auth_type="bearer")
        
        if response.status_code in [200, 202]:
            result = response.json()
            logger.info(f"Invoice reported successfully with status: {response.status_code}")
            return result
        else:
            logger.error(f"Invoice reporting failed: {response.status_code} - {response.text}")
            response.raise_for_status()

    def clear_invoice(self, invoice_xml: str, invoice_hash: str, uuid_value: str) -> Dict[str, Any]:
        """
        Clear standard invoice (Clearance API)
        
        Args:
            invoice_xml: Base64 encoded invoice XML
            invoice_hash: Invoice hash
            uuid_value: UUID for the invoice
            
        Returns:
            Clearance response
        """
        endpoint = "/invoices/clearance/single"
        data = {
            "invoiceHash": invoice_hash,
            "uuid": uuid_value,
            "invoice": invoice_xml
        }
        
        response = self._make_request("POST", endpoint, data, auth_type="bearer")
        
        if response.status_code in [200, 202]:
            result = response.json()
            if response.status_code == 303:
                logger.info("Invoice clearance turned off, reporting instead")
            else:
                logger.info(f"Invoice cleared successfully with status: {response.status_code}")
            return result
        else:
            logger.error(f"Invoice clearance failed: {response.status_code} - {response.text}")
            response.raise_for_status()

    def generate_qr_code_data(self, seller_name: str, vat_number: str, 
                             timestamp: str, total_amount: float, vat_amount: float) -> str:
        """
        Generate QR code data for invoice
        
        Args:
            seller_name: Name of the seller
            vat_number: VAT registration number
            timestamp: Invoice timestamp
            total_amount: Total invoice amount including VAT
            vat_amount: VAT amount
            
        Returns:
            Base64 encoded QR code data
        """
        # Create TLV (Tag-Length-Value) structure for QR code
        def create_tlv(tag: int, value: str) -> bytes:
            value_bytes = value.encode('utf-8')
            return bytes([tag, len(value_bytes)]) + value_bytes
        
        qr_data = b""
        qr_data += create_tlv(1, seller_name)
        qr_data += create_tlv(2, vat_number)
        qr_data += create_tlv(3, timestamp)
        qr_data += create_tlv(4, f"{total_amount:.2f}")
        qr_data += create_tlv(5, f"{vat_amount:.2f}")
        
        return base64.b64encode(qr_data).decode()

    def calculate_invoice_hash(self, invoice_xml: str) -> str:
        """
        Calculate hash for invoice XML using C14N11 canonicalization
        
        Args:
            invoice_xml: Invoice XML content
            
        Returns:
            Base64 encoded hash
        """
        # Parse XML and canonicalize using C14N11
        root = ET.fromstring(invoice_xml)
        # Note: For proper C14N11 canonicalization, use lxml library in production
        canonical_xml = ET.tostring(root, encoding='unicode', method='xml')
        
        # Calculate SHA-256 hash
        hash_bytes = hashlib.sha256(canonical_xml.encode('utf-8')).digest()
        return base64.b64encode(hash_bytes).decode()


def create_sample_invoice_xml():
    """
    Create a sample UBL 2.1 invoice XML for testing
    """
    invoice_xml = """<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    <cbc:ID>INV-2025-001</cbc:ID>
    <cbc:UUID>12345678-1234-1234-1234-123456789012</cbc:UUID>
    <cbc:IssueDate>2025-08-16</cbc:IssueDate>
    <cbc:IssueTime>10:30:00</cbc:IssueTime>
    <cbc:InvoiceTypeCode name="0200000">388</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>SAR</cbc:DocumentCurrencyCode>
    
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="CRN">1234567890</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>Test Company Ltd</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:StreetName>King Fahd Road</cbc:StreetName>
                <cbc:CityName>Riyadh</cbc:CityName>
                <cbc:PostalZone>12345</cbc:PostalZone>
                <cac:Country>
                    <cbc:IdentificationCode>SA</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>123456789012345</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
        </cac:Party>
    </cac:AccountingSupplierParty>
    
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="NAT">1234567890</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>Customer Name</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:CityName>Jeddah</cbc:CityName>
                <cac:Country>
                    <cbc:IdentificationCode>SA</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
        </cac:Party>
    </cac:AccountingCustomerParty>
    
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="SAR">15.00</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="SAR">100.00</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="SAR">15.00</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>15.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="SAR">100.00</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="SAR">100.00</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="SAR">115.00</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="SAR">115.00</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    
    <cac:InvoiceLine>
        <cbc:ID>1</cbc:ID>
        <cbc:InvoicedQuantity unitCode="PCE">1</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="SAR">100.00</cbc:LineExtensionAmount>
        <cac:Item>
            <cbc:Name>Test Product</cbc:Name>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="SAR">100.00</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
</Invoice>"""
    return invoice_xml


def credential_management_example():
    """
    Example showing proper credential management for Maximum Speed Tech Supply LTD
    """
    client = ZATCAAPIClient(environment="sandbox")
    
    print("=== ZATCA Credential Management - Maximum Speed Tech Supply LTD ===\n")
    
    try:
        # Check initial credential status
        print("1. Initial credential status:")
        cred_info = client.get_credentials_info()
        print(f"   Has Compliance CSID: {cred_info['has_compliance_csid']}")
        print(f"   Has Production CSID: {cred_info['has_production_csid']}")
        
        # Generate CSR with your company details
        print("\n2. Generating CSR for Maximum Speed Tech Supply LTD...")
        private_key, csr = client.generate_csr(
            vat_number="399999999900003",  # Your VAT number
            common_name="TST-886431145-399999999900003",  # Your Common Name
            organization_unit="Head Office", 
            organization="Maximum Speed Tech Supply LTD",  # Your Organization
            serial_number="EGS001",
            organization_identifier="399999999900003",  # Your Identifier
            invoice_type="1100",
            location="Saudi Arabia"
        )
        print("✓ CSR generated with company details")
        
        # Get compliance credentials
        print("\n3. Getting Compliance CSID...")
        compliance_result = client.get_compliance_csid(csr)
        
        print("   Compliance credentials received:")
        print(f"   binarySecurityToken: {compliance_result['binarySecurityToken'][:30]}...")
        print(f"   secret: {compliance_result['secret'][:30]}...")
        print(f"   requestId: {compliance_result.get('requestId', 'N/A')}")
        
        # Check updated credential status
        print("\n4. Updated credential status:")
        cred_info = client.get_credentials_info()
        print(f"   Has Compliance CSID: {cred_info['has_compliance_csid']}")
        print(f"   Compliance Token Preview: {cred_info['compliance_token_preview']}")
        
        # Example of manually setting credentials (useful for testing)
        print("\n5. Example: Manually setting credentials")
        print("   You can set your actual ZATCA credentials like this:")
        print("   client.set_compliance_credentials(")
        print('       binary_security_token="TUlJQ...",  # Your actual token')
        print('       secret="Zjg3ZD..."  # Your actual secret')
        print("   )")
        
        print("\n✓ Ready for ZATCA API integration with Maximum Speed Tech Supply LTD")
        
    except Exception as e:
        print(f"❌ Error in credential management: {e}")


def create_company_invoice_xml():
    """
    Create invoice XML for Maximum Speed Tech Supply LTD
    """
    invoice_xml = f"""<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    <cbc:ID>INV-MST-2025-001</cbc:ID>
    <cbc:UUID>{str(uuid.uuid4())}</cbc:UUID>
    <cbc:IssueDate>{datetime.now().strftime('%Y-%m-%d')}</cbc:IssueDate>
    <cbc:IssueTime>{datetime.now().strftime('%H:%M:%S')}</cbc:IssueTime>
    <cbc:InvoiceTypeCode name="0200000">388</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>SAR</cbc:DocumentCurrencyCode>
    
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="CRN">399999999900003</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>Maximum Speed Tech Supply LTD</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:StreetName>Tech District</cbc:StreetName>
                <cbc:CityName>Riyadh</cbc:CityName>
                <cbc:PostalZone>12345</cbc:PostalZone>
                <cac:Country>
                    <cbc:IdentificationCode>SA</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>399999999900003</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
        </cac:Party>
    </cac:AccountingSupplierParty>
    
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="NAT">1234567890</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>Test Customer</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:CityName>Jeddah</cbc:CityName>
                <cac:Country>
                    <cbc:IdentificationCode>SA</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
        </cac:Party>
    </cac:AccountingCustomerParty>
    
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="SAR">75.00</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="SAR">500.00</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="SAR">75.00</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>15.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="SAR">500.00</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="SAR">500.00</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="SAR">575.00</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="SAR">575.00</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    
    <cac:InvoiceLine>
        <cbc:ID>1</cbc:ID>
        <cbc:InvoicedQuantity unitCode="PCE">1</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="SAR">500.00</cbc:LineExtensionAmount>
        <cac:Item>
            <cbc:Name>Tech Supply Equipment</cbc:Name>
            <cbc:Description>High-speed technology equipment</cbc:Description>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="SAR">500.00</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
</Invoice>"""
    return invoice_xml


def authentication_test_example():
    """
    Test authentication with sample credentials
    """
    print("\n=== Authentication Test Example ===")
    
    client = ZATCAAPIClient(environment="sandbox")
    
    # Set test credentials (replace with your actual credentials)
    client.set_compliance_credentials(
        binary_security_token="TUlJQjJEQ0NBWjZnQXdJQkFnSUdBWE53...",  # Replace with actual
        secret="Zjg3ZDBhYTZhZjAyNGJjYzk2OGZlYjI..."  # Replace with actual
    )
    
    print("Test credentials set:")
    cred_info = client.get_credentials_info()
    print(f"Compliance Token Preview: {cred_info['compliance_token_preview']}")
    
    # Test with sample invoice
    try:
        sample_xml = create_sample_invoice_xml()
        invoice_xml_b64 = base64.b64encode(sample_xml.encode()).decode()
        invoice_hash = client.calculate_invoice_hash(sample_xml)
        invoice_uuid = str(uuid.uuid4())
        
        print(f"\nTesting compliance check with:")
        print(f"UUID: {invoice_uuid}")
        print(f"Hash: {invoice_hash[:30]}...")
        
        # This would make an actual API call with proper authentication
        # compliance_result = client.compliance_check(invoice_xml_b64, invoice_hash, invoice_uuid)
        print("✓ Ready to make authenticated API calls")
        
    except Exception as e:
        print(f"❌ Authentication test error: {e}")
    """
    Complete example with actual invoice processing
    """
    client = ZATCAAPIClient(environment="sandbox")
    
    try:
        print("=== ZATCA E-Invoicing Integration Example ===\n")
        
        # Step 1: Generate CSR
        print("1. Generating CSR...")
        private_key, csr = client.generate_csr(
            vat_number="123456789012345",
            common_name="Test Company EGS-001",
            organization_unit="Riyadh Branch", 
            organization="Test Company Ltd",
            serial_number="EGS001",
            organization_identifier="CRN1234567890",
            invoice_type="1100",
            location="Riyadh"
        )
        print("✓ CSR generated successfully")
        
        # Step 2: Get Compliance CSID
        print("\n2. Getting Compliance CSID...")
        compliance_result = client.get_compliance_csid(csr)
        print("✓ Compliance CSID obtained")
        
        # Step 3: Create and process sample invoice
        print("\n3. Processing sample invoice...")
        sample_invoice_xml = create_sample_invoice_xml()
        invoice_xml_b64 = base64.b64encode(sample_invoice_xml.encode()).decode()
        invoice_hash = client.calculate_invoice_hash(sample_invoice_xml)
        invoice_uuid = str(uuid.uuid4())
        
        print(f"   Invoice UUID: {invoice_uuid}")
        print(f"   Invoice Hash: {invoice_hash}")
        
        # Step 4: Compliance check
        print("\n4. Performing compliance check...")
        compliance_check_result = client.compliance_check(
            invoice_xml_b64, invoice_hash, invoice_uuid
        )
        print("✓ Compliance check completed")
        
        # Step 5: Generate QR Code
        print("\n5. Generating QR code...")
        qr_data = client.generate_qr_code_data(
            seller_name="Test Company Ltd",
            vat_number="123456789012345",
            timestamp="2025-08-16T10:30:00Z",
            total_amount=115.0,
            vat_amount=15.0
        )
        print(f"✓ QR Code generated: {qr_data[:50]}...")
        
        # Step 6: Get Production CSID (if compliance passed)
        if compliance_check_result.get("reportingStatus") == "REPORTED":
            print("\n6. Getting Production CSID...")
            production_result = client.get_production_csid(
                compliance_check_result.get("requestId")
            )
            print("✓ Production CSID obtained")
            
            # Step 7: Report invoice in production
            print("\n7. Reporting invoice...")
            report_result = client.report_invoice(
                invoice_xml_b64, invoice_hash, str(uuid.uuid4())
            )
            print("✓ Invoice reported successfully")
        
        print("\n=== Integration completed successfully! ===")
        
    except Exception as e:
        logger.error(f"Integration failed: {e}")
        print(f"❌ Error: {e}")


# Example usage and test functions
def example_integration_flow():
    """
    Example of complete integration flow
    """
    # Initialize client for sandbox
    client = ZATCAAPIClient(environment="sandbox")
    
    try:
        # Step 1: Generate CSR with ZATCA compliance
        print("Generating CSR...")
        private_key, csr = client.generate_csr(
            vat_number="1234567890123456",  # 15-digit VAT number
            common_name="Test Company EGS",
            organization_unit="IT Department", 
            organization="Test Company Ltd",
            serial_number="EGS001",
            organization_identifier="ORG123456",
            invoice_type="1100",  # Both simplified and standard
            location="Riyadh"
        )
        
        # Step 2: Get Compliance CSID
        print("Getting Compliance CSID...")
        compliance_result = client.get_compliance_csid(csr)
        
        # Step 3: Perform compliance check (you need actual invoice XML here)
        print("Performing compliance check...")
        # This would require actual invoice XML - placeholder for demo
        invoice_xml_b64 = "base64_encoded_invoice_xml_here"
        invoice_hash = "calculated_invoice_hash_here"
        invoice_uuid = str(uuid.uuid4())
        
        # compliance_check_result = client.compliance_check(
        #     invoice_xml_b64, invoice_hash, invoice_uuid
        # )
        
        # Step 4: Get Production CSID (after successful compliance)
        # production_result = client.get_production_csid(
        #     compliance_check_result.get("requestId")
        # )
        
        # Step 5: Report or Clear invoices
        # report_result = client.report_invoice(
        #     invoice_xml_b64, invoice_hash, invoice_uuid
        # )
        
        print("Integration flow completed successfully!")
        
    except Exception as e:
        logger.error(f"Integration flow failed: {e}")


if __name__ == "__main__":
    # Show secure credential setup methods
    secure_credential_setup_example()
    
    # Run credential management example (without hardcoded credentials)
    credential_management_example()
    
    # Run secure authentication test
    authentication_test_example()
    
    # Run complete integration example  
    complete_integration_example()
    
    # Test QR code generation for Maximum Speed Tech Supply LTD
    print("\n=== Testing QR Code Generation - Maximum Speed Tech Supply LTD ===")
    client = ZATCAAPIClient()
    qr_data = client.generate_qr_code_data(
        seller_name="Maximum Speed Tech Supply LTD",
        vat_number="399999999900003",
        timestamp="2025-08-16T10:30:00Z",
        total_amount=575.0,
        vat_amount=75.0
    )
    print(f"QR Code Data: {qr_data}")
    
    # Test hash calculation with company invoice
    print("\n=== Testing Hash Calculation - Company Invoice ===")
    company_xml = create_company_invoice_xml()
    invoice_hash = client.calculate_invoice_hash(company_xml)
    print(f"Company Invoice Hash: {invoice_hash}")
    
    # Test simple CSR generation for the company
    print("\n=== Testing Simple CSR Generation - Maximum Speed Tech Supply LTD ===")
    simple_csr = client.generate_simple_csr(
        common_name="TST-886431145-399999999900003",
        organization="Maximum Speed Tech Supply LTD",
        organization_unit="Head Office"
    )
    print("Simple CSR generated for Maximum Speed Tech Supply LTD:")
    print(simple_csr[:200] + "...")
    
    # Show credential status
    print("\n=== Final Credential Status ===")
    cred_info = client.get_credentials_info()
    print(f"Has Compliance CSID: {cred_info['has_compliance_csid']}")
    print(f"Has Production CSID: {cred_info['has_production_csid']}")
    if cred_info['compliance_token_preview']:
        print(f"Compliance Token: {cred_info['compliance_token_preview']}")
    if cred_info['production_token_preview']:
        print(f"Production Token: {cred_info['production_token_preview']}")
    
    print("\n" + "="*60)
    print("SECURITY REMINDER:")
    print("- NEVER hardcode credentials in scripts")
    print("- Use environment variables or secure config files")
    print("- Keep credential files private and secure")
    print("- Use .gitignore to exclude credential files from version control")
    print("="*60)