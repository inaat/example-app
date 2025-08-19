@extends('layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Company Debits')
@section('page-title', 'Company Debit Notes')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('zatca.company.invoices.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-file-invoice me-2"></i>View All Invoices
        </a>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-success mb-4">
            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Company Debit Notes (Additional Charges)</h6>
            <p class="mb-0">This page shows all debit notes (invoice type 381) created for additional charges, fees, and billing corrections. Debit notes increase the customer's owed amount.</p>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Debit Note #</th>
                        <th>Original Invoice</th>
                        <th>Company</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Additional Amount</th>
                        <th>Reason</th>
                        <th>ZATCA Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>
                                <strong style="color: #198754;">{{ $invoice->invoice_number }}</strong>
                                <br>
                                <small class="text-muted">{{ Str::limit($invoice->uuid, 20) }}</small>
                            </td>
                            <td>
                                @if($invoice->original_invoice_number)
                                    <strong>{{ $invoice->original_invoice_number }}</strong>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $invoice->company->organization_name }}</strong><br>
                                <small class="text-muted">{{ $invoice->company->vat_number }}</small>
                            </td>
                            <td>
                                {{ $invoice->issue_date->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $invoice->issue_time }}</small>
                            </td>
                            <td>
                                @if($invoice->buyer_info)
                                    <strong>{{ $invoice->buyer_info['name'] }}</strong><br>
                                    <small class="text-muted">{{ $invoice->buyer_info['vat_number'] ?? 'N/A' }}</small>
                                @else
                                    <span class="text-muted">No customer</span>
                                @endif
                            </td>
                            <td>
                                <strong style="color: #198754;">+{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</strong><br>
                                <small class="text-muted">Tax: {{ number_format($invoice->tax_amount, 2) }}</small>
                            </td>
                            <td>
                                @if($invoice->return_reason)
                                    <span class="badge bg-success">{{ $invoice->return_reason }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $invoice->getStatusBadgeColor() }}">
                                    {{ ucfirst($invoice->zatca_status ?? 'pending') }}
                                </span>
                                @if($invoice->submitted_at)
                                    <br><small class="text-muted">{{ $invoice->submitted_at->format('M d, H:i') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('zatca.company.debits.show', $invoice) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('zatca.company.debits.print', $invoice) }}" 
                                       class="btn btn-sm btn-outline-secondary" title="Print" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    
                                    @if($invoice->isPending())
                                        <form action="{{ route('zatca.company.debits.destroy', $invoice) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this debit note?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-plus-circle fa-2x mb-3" style="color: #198754;"></i><br>
                                No debit notes found. Debit notes are created when adding charges to <a href="{{ route('zatca.company.invoices.index') }}">company invoices</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="d-flex justify-content-center">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection