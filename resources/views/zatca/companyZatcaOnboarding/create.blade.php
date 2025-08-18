@extends('layouts.app')

@section('title', 'Create Certificate')
@section('page-title', 'Create ZATCA Certificate')

@section('content')

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-3 font-weight-bold text-primary">ZATCA Onboarding</h1>
            <div class="mb-2">
                <p class="text-muted mb-2">Connect with the Zakat, Tax and Customs Authority by completing the onboarding process.</p>
                <p class="text-muted mb-2">Please carefully fill in the data for each branch to establish the connection with ZATCA.</p>
                <div class="alert alert-info py-2 mb-0">
                    <i class="fa fa-info-circle mr-2"></i>
                    <strong>Important:</strong> When using in production, select Portal mode as "Core".
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <h2 class="h4 font-weight-bold">{{ $onboarding->organization_name ?? 'New Company' }}</h2>
                </div>
                <div class="col-md-9">
                    <div class="alert alert-success mb-0">
                        <span>Portal Mode:</span> {{ ucwords(str_replace('-', ' ', $onboarding->portal_mode ?? 'Developer Portal')) }},
                        <span>Status:</span> {{ ucfirst($onboarding->status ?? 'Pending') }}
                    </div>
                </div>
            </div>
                                <form method="POST" action="{{ route('company-onboarding.store') }}" accept-charset="UTF-8" id="details_1" enctype="multipart/form-data">
                                    @csrf

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="portal_mode0">Portal Mode</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="When using in live, select Portal mode as Core." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <select class="form-control" required="" id="portal_mode0" name="portal_mode">
                                                <option value="">Please Select</option>
                                                <option value="developer-portal" {{ ($onboarding->portal_mode ?? 'developer-portal') == 'developer-portal' ? 'selected' : '' }}>Developer Portal</option>
                                                <option value="simulation" {{ ($onboarding->portal_mode ?? '') == 'simulation' ? 'selected' : '' }}>Simulation Mode (Testing)</option>
                                                <option value="core" {{ ($onboarding->portal_mode ?? '') == 'core' ? 'selected' : '' }}>Core Mode (Live)</option>
                                            </select>
                                            <button type="button" class="btn btn-info btn-sm mt-2 fill-test-data" data-index="0" style="display: none;">
                                                Fill Test Data
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="otp0">One-Time Password (OTP)</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Developer Portal sanbox accept any otp, Simulation accept otp from fatoora portal, Core mean live accept otp from fatoora portal" data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" required="" placeholder="One-Time Password (OTP)" id="OTP0" name="otp" type="number" value="{{ $onboarding->otp ?? '111222' }}">

                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="email0">Email</label>
                                            <input class="form-control" required="" placeholder="Email" id="email0" name="email" type="email" value="{{ $onboarding->email ?? 'info@company.com' }}">
                                            <small class="form-text text-muted">Use the email registered with your ZATCA account.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="common_name0">Common Name</label>
                                            <input class="form-control" required="" placeholder="Common Name" id="common_name0" name="common_name" type="text" value="{{ $onboarding->common_name ?? '' }}">
                                            <small class="form-text text-muted">A unique identifier for your solution/device.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="country_code0">Country Code</label>
                                            <input class="form-control" readonly="" name="country_code" type="text" value="{{ $onboarding->country_code ?? 'SA' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="organization_unit_name0">Organization Unit Name</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Branch name or company name" data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="Organization Unit Name" name="organization_unit_name" type="text" value="{{ $onboarding->organization_unit_name ?? '' }}">
                                            <small class="form-text text-muted">For <strong>VAT groups</strong>: Enter the 10-digit TIN of the group member.<br>For <strong>regular taxpayers</strong>: Enter your branch name.</small>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="organization_name0">Organization Name</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Branch name or company name" data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="Organization Name" name="organization_name" type="text" value="{{ $onboarding->organization_name ?? '' }}">
                                            <small class="form-text text-muted">The full name of your company or taxpayer entity.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="egs_serial_number0">EGS Serial Number</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Enter the EGS serial numbers. Use | to separate multiple values if needed." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="EGS Serial Number" name="egs_serial_number" type="text" value="{{ $onboarding->egs_serial_number ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="vat_number0">VAT Number</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Enter your VAT number as provided by the tax authority." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="VAT Number" name="vat_number" type="text" value="{{ $onboarding->vat_number ?? '' }}">
                                            <small class="form-text text-muted">Format : <strong>15 digits</strong>, starts and ends with <strong>3</strong>.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="vat_name0">VAT Name</label>
                                            <input class="form-control" placeholder="VAT Name" name="vat_name" type="text" value="{{ $onboarding->vat_name ?? '' }}">
                                            <small class="form-text text-muted">Enter the VAT name tied to your organization.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="invoice_type0">Invoice Type</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Select the invoice type you wish to generate." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <select class="form-control" name="invoice_type">
                                                <option value="1100" {{ ($onboarding->invoice_type ?? '1100') == '1100' ? 'selected' : '' }}>Together (B2B &amp; B2C Invoice)</option>
                                                <option value="0100" {{ ($onboarding->invoice_type ?? '') == '0100' ? 'selected' : '' }}>Simplified Invoice (B2C)</option>
                                                <option value="1000" {{ ($onboarding->invoice_type ?? '') == '1000' ? 'selected' : '' }}>Standard Invoice (B2B)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="registered_address0">Registered Address</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Enter the short registered address from your national address card." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="Registered Address" name="registered_address" type="text" value="{{ $onboarding->registered_address ?? '' }}">

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="business_category0">Business Category</label>
                                            <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" data-container="body" data-toggle="popover" data-placement="auto bottom" data-content="Enter your business category (e.g., Transportations)." data-html="true" data-trigger="hover" data-original-title="" title=""></i>                                            <input class="form-control" placeholder="Business Category" name="business_category" type="text" value="{{ $onboarding->business_category ?? '' }}">
                                            <small class="form-text text-muted">Specify the sector in which invoices are issued.<br><strong>Examples</strong>: Retail, Services, etc.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="crn0">CRN</label>
                                            <input class="form-control" placeholder="CRN" name="crn" type="text" value="{{ $onboarding->crn ?? '' }}">
                                            <small class="form-text text-muted"><strong>Commercial Registration Number</strong><br><strong>Format</strong>: CRN101012345<br>→ Must match your official registration.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="street_name0">Street Name</label>
                                            <input class="form-control" placeholder="Street Name" name="street_name" type="text" value="{{ $onboarding->street_name ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="building_number0">Building Number</label>
                                            <input class="form-control" placeholder="Building Number" name="building_number" type="text" value="{{ $onboarding->building_number ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <!-- Additional ZATCA Address Details -->


                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="plot_identification0">Plot Identification/Secondary Number</label>
                                            <input class="form-control" placeholder="Plot Identification" name="plot_identification" type="text" value="{{ $onboarding->plot_identification ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="sub_division_name0">Sub Division Name/District</label>
                                            <input class="form-control" placeholder="Sub Division Name" name="sub_division_name" type="text" value="{{ $onboarding->sub_division_name ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="city_name0">City Name</label>
                                            <input class="form-control" placeholder="City Name" name="city_name" type="text" value="{{ $onboarding->city_name ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="postal_number0">Postal Number/Zip Code</label>
                                            <input class="form-control" placeholder="Postal Number" name="postal_number" type="text" value="{{ $onboarding->postal_number ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="country_name0">Country Name</label>
                                            <input class="form-control" placeholder="Country Name" name="country_name" type="text" value="{{ $onboarding->country_name ?? 'Saudi Arabia' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button type="submit" class="btn btn-success btn-lg px-5">Submit</button>
                                    </div>
                                </div>
            </form>
        </div>
    </div>
</div>

@endsection