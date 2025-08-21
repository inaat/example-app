@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Customers</h4>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Customer
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($customers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>VAT Number</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>City</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                        <tr>
                                            <td>
                                                <strong>{{ $customer->name }}</strong>
                                            </td>
                                            <td>{{ $customer->vat_number ?? '-' }}</td>
                                            <td>{{ $customer->email ?? '-' }}</td>
                                            <td>{{ $customer->phone ?? '-' }}</td>
                                            <td>{{ $customer->city ?? '-' }}</td>
                                            <td>
                                                @if($customer->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('customers.show', $customer) }}" 
                                                       class="btn btn-sm btn-outline-info">View</a>
                                                    <a href="{{ route('customers.edit', $customer) }}" 
                                                       class="btn btn-sm btn-outline-primary">Edit</a>
                                                    @if($customer->invoices_count == 0)
                                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" 
                                                              style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this customer?')">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $customers->links() }}
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">No customers found.</p>
                            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                Add Your First Customer
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection