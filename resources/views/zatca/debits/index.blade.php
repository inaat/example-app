@extends('layouts.app')

@section('title', 'Debit Notes')
@section('page-title', 'Debit Notes (Additional Charges)')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('zatca.debits.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Debit Note
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
                        <th>Debit Note #</th>
                        <th>Original Invoice</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>ZATCA Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($debitNotes as $debitNote)
                        <tr>
                            <td>
                                <strong>{{ $debitNote->invoice_number }}</strong><br>
                                <small class="text-muted">{{ Str::limit($debitNote->uuid, 20) }}</small>
                            </td>
                            <td>
                                <strong>{{ $debitNote->original_invoice_number }}</strong>
                                @if($debitNote->originalInvoice)
                                    <br><small class="text-muted">
                                        <a href="{{ route('zatca.invoices.show', $debitNote->originalInvoice) }}" class="text-decoration-none">
                                            View Original
                                        </a>
                                    </small>
                                @endif
                            </td>
                            <td>
                                {{ $debitNote->issue_date->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $debitNote->issue_time }}</small>
                            </td>
                            <td>
                                @if($debitNote->buyer_info)
                                    <strong>{{ $debitNote->buyer_info['name'] }}</strong><br>
                                    <small class="text-muted">{{ $debitNote->buyer_info['vat_number'] ?? 'N/A' }}</small>
                                @else
                                    <span class="text-muted">No customer</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">+{{ number_format($debitNote->total_amount, 2) }} {{ $debitNote->currency }}</strong><br>
                                <small class="text-muted">Tax: {{ number_format($debitNote->tax_amount, 2) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($debitNote->debit_type ?? 'N/A') }}</span><br>
                                <small class="text-muted">{{ Str::limit($debitNote->debit_reason ?? 'N/A', 30) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $debitNote->getStatusBadgeColor() }}">
                                    {{ ucfirst($debitNote->zatca_status) }}
                                </span>
                                @if($debitNote->submitted_at)
                                    <br><small class="text-muted">{{ $debitNote->submitted_at->format('M d, H:i') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('zatca.debits.show', $debitNote) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('zatca.debits.print', $debitNote) }}" 
                                       class="btn btn-sm btn-outline-info" title="Print" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    
                                    @if($debitNote->isPending())
                                        <form action="{{ route('zatca.debits.destroy', $debitNote) }}" 
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
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-file-invoice-dollar fa-2x mb-3"></i><br>
                                No debit notes found. <a href="{{ route('zatca.debits.create') }}">Create your first debit note</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($debitNotes->hasPages())
            <div class="d-flex justify-content-center">
                {{ $debitNotes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection