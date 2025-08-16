@extends('layouts.app')

@section('title', 'ZATCA Invoices')
@section('page-title', 'ZATCA Invoices')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('zatca.invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Invoice
        </a>
        <a href="{{ route('zatca.returns.create') }}" class="btn btn-outline-warning">
            <i class="fas fa-undo me-2"></i>Create Return
        </a>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Invoice #</th>
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
                                <br>
                                <small class="text-muted">{{ Str::limit($invoice->uuid, 20) }}</small>
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
                                    {{ ucfirst($invoice->zatca_status) }}
                                </span>
                                @if($invoice->submitted_at)
                                    <br><small class="text-muted">{{ $invoice->submitted_at->format('M d, H:i') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('zatca.invoices.show', $invoice) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($invoice->zatca_status === 'reported' || $invoice->zatca_status === 'cleared')
                                        <a href="{{ route('zatca.returns.create-from', $invoice) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Create Return">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    @endif
                                    
                                    @if($invoice->isPending())
                                        <form action="{{ route('zatca.invoices.destroy', $invoice) }}" 
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
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-file-invoice fa-2x mb-3"></i><br>
                                No invoices found. <a href="{{ route('zatca.invoices.create') }}">Create your first invoice</a>.
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