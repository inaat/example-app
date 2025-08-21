@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Customer: {{ $customer->name }}</h4>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('customers.update', $customer) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Customer Name *</label>
                                <input type="text" name="name" id="name" class="form-control" 
                                       value="{{ old('name', $customer->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="vat_number" class="form-label">VAT Number</label>
                                <input type="text" name="vat_number" id="vat_number" class="form-control" 
                                       value="{{ old('vat_number', $customer->vat_number) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" 
                                       value="{{ old('email', $customer->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control" 
                                       value="{{ old('phone', $customer->phone) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea name="address" id="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" name="city" id="city" class="form-control" 
                                       value="{{ old('city', $customer->city) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control" 
                                       value="{{ old('postal_code', $customer->postal_code) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="country" class="form-label">Country *</label>
                                <select name="country" id="country" class="form-control" required>
                                    <option value="Saudi Arabia" {{ old('country', $customer->country) == 'Saudi Arabia' ? 'selected' : '' }}>Saudi Arabia</option>
                                    <option value="UAE" {{ old('country', $customer->country) == 'UAE' ? 'selected' : '' }}>UAE</option>
                                    <option value="Kuwait" {{ old('country', $customer->country) == 'Kuwait' ? 'selected' : '' }}>Kuwait</option>
                                    <option value="Qatar" {{ old('country', $customer->country) == 'Qatar' ? 'selected' : '' }}>Qatar</option>
                                    <option value="Bahrain" {{ old('country', $customer->country) == 'Bahrain' ? 'selected' : '' }}>Bahrain</option>
                                    <option value="Oman" {{ old('country', $customer->country) == 'Oman' ? 'selected' : '' }}>Oman</option>
                                    <option value="Other" {{ old('country', $customer->country) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                           {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Customer
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $customer->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection