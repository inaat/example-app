@extends('layouts.app')

@section('title', 'Return Invoices')
@section('page-title', 'Return Invoices (Credit Notes)')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Return Invoices</h5>
                <a href="{{ route('zatca.returns.create') }}" class="btn btn-primary">
                    <i class="fas fa-undo me-2"></i>Create Return Invoice
                </a>
            </div>
            <div class="card-body">
                @if($returnInvoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Return Number</th>
                                    <th>Original Invoice</th>
                                    <th>Return Reason</th>
                                    <th>Return Type</th>
                                    <th>Return Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returnInvoices as $invoice)
                                    <tr>
                                        <td>
                                            <strong>{{ $invoice->invoice_number }}</strong><br>
                                            <small class="text-muted">{{ Str::limit($invoice->uuid, 20) }}</small>
                                        </td>
                                        <td>
                                            @if($invoice->original_invoice_number)
                                                <span class="badge bg-secondary">{{ $invoice->original_invoice_number }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($invoice->return_reason ?? 'N/A', 30) }}</td>
                                        <td>
                                            @if($invoice->return_type)
                                                <span class="badge bg-{{ $invoice->return_type === 'full' ? 'warning' : 'info' }}">
                                                    {{ ucfirst($invoice->return_type) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $invoice->issue_date->format('M d, Y') }}</td>
                                        <td class="text-danger">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                                        <td>
                                            <span class="badge bg-{{ $invoice->getStatusBadgeColor() }}">
                                                {{ ucfirst($invoice->zatca_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('zatca.returns.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-undo-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No return invoices found.</p>
                        <a href="{{ route('zatca.returns.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create First Return
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection