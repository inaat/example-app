<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ZATCA E-Invoice System')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .content {
            min-height: 100vh;
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white !important;
        }
        .status-badge {
            font-size: 0.8em;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="px-3 text-muted">ZATCA E-Invoice</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('zatca.onboarding.*') ? 'active' : '' }}" 
                               href="{{ route('zatca.onboarding.index') }}">
                                <i class="fas fa-certificate me-2"></i>
                                Certificates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('zatca.invoices.*') ? 'active' : '' }}" 
                               href="{{ route('zatca.invoices.index') }}">
                                <i class="fas fa-file-invoice me-2"></i>
                                Invoices
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('zatca.returns.*') ? 'active' : '' }}" 
                               href="{{ route('zatca.returns.index') }}">
                                <i class="fas fa-undo me-2"></i>
                                Returns
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('zatca.debits.*') ? 'active' : '' }}" 
                               href="{{ route('zatca.debits.index') }}">
                                <i class="fas fa-plus-circle me-2"></i>
                                Debit Notes
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="text-muted">
                    <h6 class="px-3 text-muted">COMPANY MANAGEMENT</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('company-onboarding.*') ? 'active' : '' }}" 
                               href="{{ route('company-onboarding.create') }}">
                                <i class="fas fa-building me-2"></i>
                                Company Onboarding
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" 
                               href="{{ route('products.index') }}">
                                <i class="fas fa-box me-2"></i>
                                Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" 
                               href="{{ route('customers.index') }}">
                                <i class="fas fa-users me-2"></i>
                                Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('zatca.company.invoices.*') ? 'active' : '' }}" 
                               href="{{ route('zatca.company.invoices.index') }}">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                Company Invoices
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('zatca.company.returns.*') ? 'active' : '' }}" 
                               href="{{ route('zatca.company.returns.index') }}">
                                <i class="fas fa-undo me-2"></i>
                                Company Returns
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('zatca.company.debits.*') ? 'active' : '' }}" 
                               href="{{ route('zatca.company.debits.index') }}">
                                <i class="fas fa-plus-circle me-2"></i>
                                Company Debits
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        @yield('page-actions')
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>