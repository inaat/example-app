@extends('layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Company Invoices')
@section('page-title', 'Company Invoices')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('zatca.company.invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Invoice
        </a>
        <a href="{{ route('zatca.company.returns.index') }}" class="btn btn-outline-warning">
            <i class="fas fa-undo me-2"></i>View Returns
        </a>
        <a href="{{ route('zatca.company.debits.index') }}" class="btn btn-outline-success">
            <i class="fas fa-plus-circle me-2"></i>View Debits
        </a>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Company Invoices Overview</h6>
            <p class="mb-0">This page shows regular invoices (388) only. For <strong>returns/credit notes</strong> visit <a href="{{ route('zatca.company.returns.index') }}" class="alert-link">Company Returns</a>, and for <strong>additional charges/debit notes</strong> visit <a href="{{ route('zatca.company.debits.index') }}" class="alert-link">Company Debits</a>.</p>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>ZATCA Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>
                                <strong>{{ $invoice->invoice_number }}</strong>
                                @if($invoice->hasReturns())
                                    <span class="badge bg-warning ms-1" title="Has returns">
                                        <i class="fas fa-undo"></i> {{ $invoice->returns->count() }}
                                    </span>
                                @endif
                                @if($invoice->hasDebits())
                                    <span class="badge bg-success ms-1" title="Has additional charges">
                                        <i class="fas fa-plus-circle"></i> {{ $invoice->debits->count() }}
                                    </span>
                                @endif
                                <br>
                                <small class="text-muted">{{ Str::limit($invoice->uuid, 20) }}</small>
                            </td>
                            <td>
                                <strong>{{ $invoice->company->organization_name }}</strong><br>
                                <small class="text-muted">{{ $invoice->company->vat_number }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $invoice->getInvoiceTypeName() }}
                                </span><br>
                                <small class="text-muted">
                                    {{ $invoice->invoice_subtype === '01' ? 'Standard' : 'Simplified' }}
                                </small>
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
                                <strong>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</strong><br>
                                <small class="text-muted">Tax: {{ number_format($invoice->tax_amount, 2) }}</small>
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
                                    <a href="{{ route('zatca.company.invoices.show', $invoice) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('zatca.company.invoices.print', $invoice) }}" 
                                       class="btn btn-sm btn-outline-secondary" title="Print" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    
                                    @if($invoice->zatca_status === 'reported' || $invoice->zatca_status === 'cleared')
                                        <a href="{{ route('zatca.company.invoices.create-return', $invoice) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Create Return">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                        <a href="{{ route('zatca.company.invoices.create-debit', $invoice) }}" 
                                           class="btn btn-sm btn-outline-success" title="Create Debit Note">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                    @endif
                                    
                                    @if($invoice->isPending())
                                        <form action="{{ route('zatca.company.invoices.destroy', $invoice) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this invoice?')">
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
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-file-invoice fa-2x mb-3"></i><br>
                                No company invoices found. <a href="{{ route('zatca.company.invoices.create') }}">Create your first invoice</a>.
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