@extends('layouts.app')

@section('title', 'Create Certificate')
@section('page-title', 'Create ZATCA Certificate')

@section('content')

<div class="box-primary tw-mb-4 tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
    <div class="tw-p-2 sm:tw-p-3">
                                    <div class="tw-flow-root tw-border-gray-200">
            <div class="">
                <div class="tw-py-2 tw-align-middle sm:tw-px-5">
                    <div class="mb-5">
                <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">On Boarding</h1>
                <p class="tw-text-sm tw-text-gray-600">Onboarding to connect with Zakat, Tax and Customs Authority.</p>
                <p class="tw-text-sm tw-text-gray-600">Please carefully fill in the data of each branch to link with the Zakat, Tax and Customs Authority.</p>
                <p class="tw-text-sm tw-text-gray-600"> <strong>Note:</strong> When using in live, select Portal mode as Core.</p>
            </div>
            <div class="nav-tabs-custom mt-5">
                <ul class="nav nav-tabs">
                                            <li class=" active ">
                            <a href="#cn_0" data-toggle="tab" aria-expanded="true">
                                Awesome Shop
                            </a>
                        </li>
                                    </ul>
                <div class="tab-content">
                                            
                        
                        <div class="tab-pane  active " id="cn_0">
                            <div class="row">
                                <div class="row">
                                    <h2 class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-black col-md-3">
                                        Awesome Shop</h2>

                                                                            <div class=" col-md-9 alert alert-success">
                                            <span>Portal Mode :</span>
                                            Developer Portal,
                                            <span>Status :</span>
                                            Success
                                        </div>
                                                                    </div>
                                <form method="POST" action="https://pos.ultimatefosters.com/zatca/onboarding/1" accept-charset="UTF-8" id="details_1" enctype="multipart/form-data"><input name="_method" type="hidden" value="PUT"><input name="_token" type="hidden" value="G9vnF2B730kYZ8xFoWyQ53pkzsrIj51MBIafz0Zf">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="portal_mode0">Portal Mode</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="When using in live, select Portal mode as Core." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <select class="form-control" required="" id="portal_mode0" name="portal_mode"><option value="">Please Select</option><option value="developer-portal" selected="selected">Developer Portal</option><option value="simulation">Simulation Mode (Testing)</option><option value="core">Core Mode (Live)</option></select>
                                            <button type="button" class="btn btn-info btn-sm mt-2 fill-test-data" data-index="0" style="display: none;">
                                                Fill Test Data
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="otp0">One-Time Password (OTP)</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Developer Portal sanbox accept any otp, Simulation accept otp from fatoora portal, Core mean live accept otp from fatoora portal" data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" required="" placeholder="One-Time Password (OTP)" id="OTP0" name="otp" type="number" value="111222">

                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="email0">Email</label>
                                            <input class="form-control" required="" placeholder="Email" id="email0" name="email" type="email" value="email@gmail.com">
                                            <small class="form-text text-muted">Use the email registered with your ZATCA account.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="common_name0">Common Name</label>
                                            <input class="form-control" required="" placeholder="Common Name" id="common_name0" name="common_name" type="text" value="TSTCO">
                                            <small class="form-text text-muted">A unique identifier for your solution/device.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="country_code0">Country Code</label>
                                            <input class="form-control" readonly="" name="country_code" type="text" value="SA">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="organization_unit_name0">Organization Unit Name</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Branch name or company name" data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="Organization Unit Name" name="organization_unit_name" type="text" value="TSTCO-SA">
                                            <small class="form-text text-muted">For <strong>VAT groups</strong>: Enter the 10-digit TIN of the group member.<br>For <strong>regular taxpayers</strong>: Enter your branch name.</small>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="organization_name0">Organization Name</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Branch name or company name" data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="Organization Name" name="organization_name" type="text" value="TSTCO-SA">
                                            <small class="form-text text-muted">The full name of your company or taxpayer entity.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="egs_serial_number0">EGS Serial Number</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Enter the EGS serial numbers. Use | to separate multiple values if needed." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="EGS Serial Number" name="egs_serial_number" type="text" value="1-SDSA|2-FGDS|3-SDFG">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="vat_number0">VAT Number</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Enter your VAT number as provided by the tax authority." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="VAT Number" name="vat_number" type="text" value="300000000000003">
                                            <small class="form-text text-muted">Format : <strong>15 digits</strong>, starts and ends with <strong>3</strong>.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="vat_name0">VAT Name</label>
                                            <input class="form-control" placeholder="VAT Name" name="vat_name" type="text" value="TSTCO VAT">
                                            <small class="form-text text-muted">Enter the VAT name tied to your organization.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="invoice_type0">Invoice Type</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Select the invoice type you wish to generate." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <select class="form-control" name="invoice_type"><option value="1100" selected="selected">Together (B2B &amp; B2C Invoice)</option><option value="0100">Simplified Invoice (B2C)</option><option value="1000">Standard Invoice (B2B)</option></select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="registered_address0">Registered Address</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Enter the short registered address from your national address card." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="Registered Address" name="registered_address" type="text" value="RMRE1234">

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="business_category0">Business Category</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Enter your business category (e.g., Transportations)." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="Business Category" name="business_category" type="text" value="Transportations">
                                            <small class="form-text text-muted">Specify the sector in which invoices are issued.<br><strong>Examples</strong>: Retail, Services, etc.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="crn0">CRN</label>
                                            <input class="form-control" placeholder="CRN" name="crn" type="text" value="CRN123456">
                                            <small class="form-text text-muted"><strong>Commercial Registration Number</strong><br><strong>Format</strong>: CRN101012345<br>→ Must match your official registration.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="street_name0">Street Name</label>
                                            <input class="form-control" placeholder="Street Name" name="street_name" type="text" value="Main Street">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="building_number0">Building Number</label>
                                            <input class="form-control" placeholder="Building Number" name="building_number" type="text" value="123">
                                        </div>
                                    </div>
                                </div>
                                <!-- Additional ZATCA Address Details -->


                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="plot_identification0">Plot Identification/Secondary Number</label>
                                            <input class="form-control" placeholder="Plot Identification" name="plot_identification" type="text" value="Plot567">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="sub_division_name0">Sub Division Name/District</label>
                                            <input class="form-control" placeholder="Sub Division Name" name="sub_division_name" type="text" value="Zone A">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="city_name0">City Name</label>
                                            <input class="form-control" placeholder="City Name" name="city_name" type="text" value="Riyadh">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="postal_number0">Postal Number/Zip Code</label>
                                            <input class="form-control" placeholder="Postal Number" name="postal_number" type="text" value="11564">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="country_name0">Country Name</label>
                                            <input class="form-control" placeholder="Country Name" name="country_name" type="text" value="Saudi Arabia">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <input class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-lg" type="submit" value="Submit">
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                                    </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</div>