@extends('layouts.app')

@section('title', 'ZATCA Certificates')
@section('page-title', 'ZATCA Certificates')

@section('page-actions')
    <a href="{{ route('zatca.onboarding.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Create Certificate
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Organization</th>
                        <th>Identifier</th>
                        <th>Environment</th>
                        <th>Status</th>
                        <th>CSR</th>
                        <th>Compliance CSID</th>
                        <th>Production CSID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certificates as $certificate)
                        <tr>
                            <td>
                                <strong>{{ $certificate->organization_name }}</strong><br>
                                <small class="text-muted">{{ $certificate->common_name }}</small>
                            </td>
                            <td>{{ $certificate->organization_identifier }}</td>
                            <td>
                                <span class="badge bg-{{ $certificate->environment_type === 'Production' ? 'danger' : 'warning' }}">
                                    {{ $certificate->environment_type }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $certificate->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($certificate->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $certificate->csr ? 'success' : 'secondary' }}">
                                    {{ $certificate->csr ? 'Generated' : 'Pending' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $certificate->ccsid_binarySecurityToken ? 'success' : 'secondary' }}">
                                    {{ $certificate->ccsid_binarySecurityToken ? 'Obtained' : 'Pending' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $certificate->pcsid_binarySecurityToken ? 'success' : 'secondary' }}">
                                    {{ $certificate->pcsid_binarySecurityToken ? 'Obtained' : 'Pending' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('zatca.onboarding.show', $certificate) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('zatca.onboarding.edit', $certificate) }}" 
                                       class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('zatca.onboarding.destroy', $certificate) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this certificate?')">
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
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-certificate fa-2x mb-3"></i><br>
                                No certificates found. <a href="{{ route('zatca.onboarding.create') }}">Create your first certificate</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($certificates->hasPages())
            <div class="d-flex justify-content-center">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection