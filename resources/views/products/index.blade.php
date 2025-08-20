@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products Management')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Product
        </a>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Products Management</h6>
            <p class="mb-0">Manage your product catalog. Products can be selected when creating invoices instead of manually entering item details.</p>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Unit Price</th>
                        <th>Tax Rate</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->description)
                                    <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($product->sku)
                                    <code>{{ $product->sku }}</code>
                                @else
                                    <span class="text-muted">No SKU</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format($product->unit_price, 2) }} SAR</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ number_format($product->tax_rate, 1) }}%</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $product->unit_of_measure }}</span>
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('products.show', $product) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('products.edit', $product) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form action="{{ route('products.destroy', $product) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-box fa-2x mb-3"></i><br>
                                No products found. <a href="{{ route('products.create') }}">Add your first product</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection