<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>{{ $title ?? 'Small Business ERP' }} | ERP System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Small Business Accounting & Inventory ERP" name="description" />
    <meta content="Antigravity" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $companySetting = \App\Services\SettingsService::getCompany();
        $favicon = $companySetting && $companySetting->favicon ? asset('storage/' . $companySetting->favicon) : asset('assets/images/favicon.ico');
    @endphp
    <link rel="shortcut icon" href="{{ $favicon }}">

    <!-- PWA Manifest & Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#5156be">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Inventory ERP">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/icons/apple-touch-icon.png') }}">

    <!-- Preloader css -->
    <link rel="stylesheet" href="{{ asset('assets/css/preloader.min.css') }}" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    <!-- Alpine.js / Livewire styles -->
    @livewireStyles

    <style>
        .badge-soft-success { background-color: rgba(43, 181, 143, 0.15); color: #2bb58f; }
        .badge-soft-danger { background-color: rgba(253, 98, 98, 0.15); color: #fd6262; }
        .badge-soft-warning { background-color: rgba(255, 191, 67, 0.15); color: #ffbf43; }
        .badge-soft-info { background-color: rgba(75, 166, 239, 0.15); color: #4ba6ef; }
        .badge-soft-primary { background-color: rgba(81, 86, 190, 0.15); color: #5156be; }
        .badge-soft-secondary { background-color: rgba(116, 120, 141, 0.15); color: #74788d; }
        .table-custom th { font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }

        /* Sortable Table Columns */
        th.sortable {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.15s ease;
        }
        th.sortable:hover {
            background-color: #f1f5f9 !important;
            color: #5156be;
        }
        th.sortable i {
            font-size: 12px;
            vertical-align: middle;
            margin-left: 4px;
        }

        /* Empty State */
        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
        }
        .empty-state-icon {
            width: 64px;
            height: 64px;
            line-height: 64px;
            border-radius: 50%;
            background-color: #f8fafc;
            color: #94a3b8;
            font-size: 32px;
            margin: 0 auto 1rem auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Form Section Header */
        .form-section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
            padding-bottom: 6px;
            margin-bottom: 14px;
            border-bottom: 1px solid #eff2f7;
        }

        /* Clean Navbar & Brand Styling */
        .navbar-brand-box {
            padding: 0 1.25rem;
            width: 250px;
            height: 70px;
            display: flex;
            align-items: center;
            background: #ffffff;
            border-right: 1px solid #e9e9ef;
        }
        .navbar-brand-box a.logo {
            display: flex;
            align-items: center;
            height: 70px;
            text-decoration: none;
            overflow: hidden;
            width: 100%;
        }
        .logo-txt {
            font-weight: 700;
            font-size: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #2a3042;
            letter-spacing: -0.2px;
        }

        /* Menu Heading & Sub-heading polish */
        .menu-title {
            padding: 16px 20px 6px 20px !important;
            pointer-events: none;
            cursor: default;
            font-size: 11px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            font-weight: 700 !important;
            color: #8c94a6 !important;
        }

        /* Sidebar Navigation & Icons */
        #sidebar-menu ul li a {
            display: flex;
            align-items: center;
            padding: 0.65rem 1.4rem;
            color: #545a6d;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        #sidebar-menu ul li a i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.75rem;
            font-size: 1.25rem;
            color: #74788d;
            transition: all 0.2s ease;
            margin-right: 6px;
        }
        #sidebar-menu ul li a:hover {
            color: #5156be;
        }
        #sidebar-menu ul li a:hover i {
            color: #5156be;
        }

        /* Active Main Link */
        #sidebar-menu ul li a.active {
            color: #5156be !important;
            font-weight: 600;
            background-color: rgba(81, 86, 190, 0.08);
            border-radius: 0 24px 24px 0;
        }
        #sidebar-menu ul li a.active i {
            color: #5156be !important;
        }

        /* Active Parent with Submenu */
        #sidebar-menu ul li.mm-active > a.has-arrow {
            color: #5156be !important;
            font-weight: 600;
        }
        #sidebar-menu ul li.mm-active > a.has-arrow i {
            color: #5156be !important;
        }

        /* Submenu Items & Active Indicators */
        #sidebar-menu ul li ul.sub-menu {
            padding: 4px 0 6px 0;
        }
        #sidebar-menu ul li ul.sub-menu li a {
            padding: 0.45rem 1.4rem 0.45rem 3.2rem;
            font-size: 13.5px;
            color: #636b7f;
            position: relative;
            transition: all 0.2s ease;
        }
        #sidebar-menu ul li ul.sub-menu li a:before {
            content: "";
            position: absolute;
            left: 1.85rem;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background-color: #bcc4d8;
            transition: all 0.2s ease;
        }
        #sidebar-menu ul li ul.sub-menu li a:hover {
            color: #5156be !important;
        }
        #sidebar-menu ul li ul.sub-menu li a:hover:before {
            background-color: #5156be;
            width: 7px;
            height: 7px;
            box-shadow: 0 0 6px rgba(81, 86, 190, 0.4);
        }
        #sidebar-menu ul li ul.sub-menu li a.active {
            color: #5156be !important;
            font-weight: 600;
            background-color: rgba(81, 86, 190, 0.08);
            border-radius: 0 20px 20px 0;
        }
        #sidebar-menu ul li ul.sub-menu li a.active:before {
            background-color: #5156be;
            width: 7px;
            height: 7px;
            box-shadow: 0 0 6px rgba(81, 86, 190, 0.4);
        }

        /* Print Styles */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 12px !important;
            }
            #page-topbar, .vertical-menu, .footer, .btn, .page-title-box .btn, .card-header .btn, .no-print, input, select, .pagination, .alert {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .page-content {
                padding: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .table-responsive {
                overflow: visible !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            table th, table td {
                border: 1px solid #dee2e6 !important;
                padding: 6px !important;
                color: #000 !important;
            }
        }
    </style>
</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex align-items-center">
                    <!-- Clean Single Brand Logo -->
                    <div class="navbar-brand-box">
                        <a href="{{ route('dashboard') }}" class="logo logo-dark">
                            <span class="logo-sm">
                                <i class="bx bx-store-alt text-primary font-size-24"></i>
                            </span>
                            <span class="logo-lg d-flex align-items-center">
                                @if($companySetting && $companySetting->main_logo)
                                    <img src="{{ asset('storage/' . $companySetting->main_logo) }}" alt="Logo" style="max-height: 38px; max-width: 170px; object-fit: contain;">
                                @else
                                    <span class="avatar-xs me-2 d-inline-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary" style="width: 32px; height: 32px; min-width: 32px;">
                                        <i class="bx bx-store-alt font-size-18"></i>
                                    </span>
                                    <span class="logo-txt fw-bold text-dark font-size-15 text-truncate" style="max-width: 165px;" title="{{ $companySetting->company_name ?? 'Small Business ERP' }}">
                                        {{ $companySetting->company_name ?? 'Small Business ERP' }}
                                    </span>
                                @endif
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                </div>

                <div class="d-flex align-items-center">

                    <button type="button" id="pwa-install-btn" class="btn btn-sm btn-soft-primary me-2 d-none align-items-center" title="Install Inventory ERP App">
                        <i class="bx bx-download me-1 font-size-15"></i> <span class="d-none d-sm-inline font-size-13 fw-semibold">Install App</span>
                    </button>

                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item bg-light-subtle border-start border-end" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-user-circle font-size-20 align-middle me-1 text-primary"></i>
                            <span class="d-none d-xl-inline-block ms-1 fw-medium">{{ auth()->user()->name ?? 'Administrator' }}</span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a class="dropdown-item" href="{{ route('settings.company') }}"><i class="mdi mdi-cog font-size-16 align-middle me-1"></i> Company Settings</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="mdi mdi-logout font-size-16 align-middle me-1 text-danger"></i> Logout</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">

            <div data-simplebar class="h-100">

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title" data-key="t-menu">MAIN NAVIGATION</li>

                        <!-- Dashboard -->
                        <li class="{{ request()->routeIs('dashboard') ? 'mm-active' : '' }}">
                            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="bx bx-home-circle"></i>
                                <span data-key="t-dashboard">Dashboard</span>
                            </a>
                        </li>

                        <!-- Accounts -->
                        <li class="{{ request()->routeIs('accounts.*') ? 'mm-active' : '' }}">
                            <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                                <i class="bx bx-wallet"></i>
                                <span data-key="t-accounts">Accounts</span>
                            </a>
                            <ul class="sub-menu {{ request()->routeIs('accounts.*') ? 'mm-show' : '' }}" aria-expanded="{{ request()->routeIs('accounts.*') ? 'true' : 'false' }}">
                                <li><a href="{{ route('accounts.index') }}" class="{{ request()->routeIs('accounts.index') ? 'active' : '' }}">Cash & Bank</a></li>
                                <li><a href="{{ route('accounts.transactions') }}" class="{{ request()->routeIs('accounts.transactions') ? 'active' : '' }}">Transactions</a></li>
                                <li><a href="{{ route('accounts.ledger') }}" class="{{ request()->routeIs('accounts.ledger') ? 'active' : '' }}">Account Ledger</a></li>
                            </ul>
                        </li>

                        <!-- Products -->
                        <li class="{{ request()->routeIs('products.*') ? 'mm-active' : '' }}">
                            <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                <i class="bx bx-package"></i>
                                <span data-key="t-products">Products</span>
                            </a>
                            <ul class="sub-menu {{ request()->routeIs('products.*') ? 'mm-show' : '' }}" aria-expanded="{{ request()->routeIs('products.*') ? 'true' : 'false' }}">
                                <li><a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.index') ? 'active' : '' }}">Products Directory</a></li>
                                <li><a href="{{ route('products.categories') }}" class="{{ request()->routeIs('products.categories') ? 'active' : '' }}">Categories</a></li>
                                <li><a href="{{ route('products.units') }}" class="{{ request()->routeIs('products.units') ? 'active' : '' }}">Units of Measure</a></li>
                                <li><a href="{{ route('products.stock') }}" class="{{ request()->routeIs('products.stock') ? 'active' : '' }}">Stock Movements</a></li>
                            </ul>
                        </li>

                        <!-- Suppliers -->
                        <li class="{{ request()->routeIs('suppliers.*') ? 'mm-active' : '' }}">
                            <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                                <i class="bx bx-user-voice"></i>
                                <span data-key="t-suppliers">Suppliers</span>
                            </a>
                        </li>

                        <!-- Customers -->
                        <li class="{{ request()->routeIs('customers.*') ? 'mm-active' : '' }}">
                            <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                                <i class="bx bx-group"></i>
                                <span data-key="t-customers">Customers</span>
                            </a>
                        </li>

                        <!-- Purchases -->
                        <li class="{{ request()->routeIs('purchases.*') ? 'mm-active' : '' }}">
                            <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                                <i class="bx bx-cart"></i>
                                <span data-key="t-purchases">Purchases</span>
                            </a>
                            <ul class="sub-menu {{ request()->routeIs('purchases.*') ? 'mm-show' : '' }}" aria-expanded="{{ request()->routeIs('purchases.*') ? 'true' : 'false' }}">
                                <li><a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.index') ? 'active' : '' }}">Purchase Invoices</a></li>
                                <li><a href="{{ route('purchases.create') }}" class="{{ request()->routeIs('purchases.create') ? 'active' : '' }}">Create Purchase</a></li>
                                <li><a href="{{ route('purchases.returns') }}" class="{{ request()->routeIs('purchases.returns') ? 'active' : '' }}">Purchase Returns</a></li>
                            </ul>
                        </li>

                        <!-- Sales -->
                        <li class="{{ request()->routeIs('sales.*') ? 'mm-active' : '' }}">
                            <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                                <i class="bx bx-receipt"></i>
                                <span data-key="t-sales">Sales</span>
                            </a>
                            <ul class="sub-menu {{ request()->routeIs('sales.*') ? 'mm-show' : '' }}" aria-expanded="{{ request()->routeIs('sales.*') ? 'true' : 'false' }}">
                                <li><a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.index') ? 'active' : '' }}">Sales Invoices</a></li>
                                <li><a href="{{ route('sales.create') }}" class="{{ request()->routeIs('sales.create') ? 'active' : '' }}">Create Sales Invoice</a></li>
                                <li><a href="{{ route('sales.returns') }}" class="{{ request()->routeIs('sales.returns') ? 'active' : '' }}">Sales Returns</a></li>
                            </ul>
                        </li>

                        <!-- Customer & Supplier Payments -->
                        <li class="{{ request()->routeIs('payments.*') ? 'mm-active' : '' }}">
                            <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                                <i class="bx bx-dollar-circle"></i>
                                <span data-key="t-payments">Payments</span>
                            </a>
                            <ul class="sub-menu {{ request()->routeIs('payments.*') ? 'mm-show' : '' }}" aria-expanded="{{ request()->routeIs('payments.*') ? 'true' : 'false' }}">
                                <li><a href="{{ route('payments.customer') }}" class="{{ request()->routeIs('payments.customer') ? 'active' : '' }}">Customer Receipts</a></li>
                                <li><a href="{{ route('payments.supplier') }}" class="{{ request()->routeIs('payments.supplier') ? 'active' : '' }}">Supplier Payments</a></li>
                            </ul>
                        </li>

                        <!-- Income -->
                        <li class="{{ request()->routeIs('income.*') ? 'mm-active' : '' }}">
                            <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('income.*') ? 'active' : '' }}">
                                <i class="bx bx-trending-up"></i>
                                <span data-key="t-income">Income</span>
                            </a>
                            <ul class="sub-menu {{ request()->routeIs('income.*') ? 'mm-show' : '' }}" aria-expanded="{{ request()->routeIs('income.*') ? 'true' : 'false' }}">
                                <li><a href="{{ route('income.index') }}" class="{{ request()->routeIs('income.index') ? 'active' : '' }}">Income Transactions</a></li>
                                <li><a href="{{ route('income.categories') }}" class="{{ request()->routeIs('income.categories') ? 'active' : '' }}">Income Categories</a></li>
                            </ul>
                        </li>

                        <!-- Expenses -->
                        <li class="{{ request()->routeIs('expenses.*') ? 'mm-active' : '' }}">
                            <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                                <i class="bx bx-trending-down"></i>
                                <span data-key="t-expenses">Expenses</span>
                            </a>
                            <ul class="sub-menu {{ request()->routeIs('expenses.*') ? 'mm-show' : '' }}" aria-expanded="{{ request()->routeIs('expenses.*') ? 'true' : 'false' }}">
                                <li><a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.index') ? 'active' : '' }}">Operating Expenses</a></li>
                                <li><a href="{{ route('expenses.categories') }}" class="{{ request()->routeIs('expenses.categories') ? 'active' : '' }}">Expense Categories</a></li>
                            </ul>
                        </li>

                        <!-- Reports -->
                        <li class="{{ request()->routeIs('reports.*') ? 'mm-active' : '' }}">
                            <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="bx bx-bar-chart-alt-2"></i>
                                <span data-key="t-reports">Reports</span>
                            </a>
                            <ul class="sub-menu {{ request()->routeIs('reports.*') ? 'mm-show' : '' }}" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
                                <li><a href="{{ route('reports.daily') }}" class="{{ request()->routeIs('reports.daily') ? 'active' : '' }}">Daily Report</a></li>
                                <li><a href="{{ route('reports.sales') }}" class="{{ request()->routeIs('reports.sales') ? 'active' : '' }}">Sales Statement</a></li>
                                <li><a href="{{ route('reports.purchases') }}" class="{{ request()->routeIs('reports.purchases') ? 'active' : '' }}">Purchase Report</a></li>
                                <li><a href="{{ route('reports.stock') }}" class="{{ request()->routeIs('reports.stock') ? 'active' : '' }}">Stock Valuation</a></li>
                                <li><a href="{{ route('reports.cashbook') }}" class="{{ request()->routeIs('reports.cashbook') ? 'active' : '' }}">Cash Book</a></li>
                                <li><a href="{{ route('reports.bankbook') }}" class="{{ request()->routeIs('reports.bankbook') ? 'active' : '' }}">Bank Book</a></li>
                                <li><a href="{{ route('reports.receivables') }}" class="{{ request()->routeIs('reports.receivables') ? 'active' : '' }}">Receivables Aging</a></li>
                                <li><a href="{{ route('reports.payables') }}" class="{{ request()->routeIs('reports.payables') ? 'active' : '' }}">Payables Aging</a></li>
                                <li><a href="{{ route('reports.profit-loss') }}" class="{{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">Profit & Loss</a></li>
                            </ul>
                        </li>

                        <!-- Settings -->
                        <li class="{{ request()->routeIs('settings.*') ? 'mm-active' : '' }}">
                            <a href="javascript: void(0);" class="has-arrow {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                <i class="bx bx-cog"></i>
                                <span data-key="t-settings">Settings</span>
                            </a>
                            <ul class="sub-menu {{ request()->routeIs('settings.*') ? 'mm-show' : '' }}" aria-expanded="{{ request()->routeIs('settings.*') ? 'true' : 'false' }}">
                                <li><a href="{{ route('settings.company') }}" class="{{ request()->routeIs('settings.company') ? 'active' : '' }}">Company Settings</a></li>
                                <li><a href="{{ route('settings.invoice') }}" class="{{ request()->routeIs('settings.invoice') ? 'active' : '' }}">Invoice Settings</a></li>
                                <li><a href="{{ route('settings.logos') }}" class="{{ request()->routeIs('settings.logos') ? 'active' : '' }}">Logo Management</a></li>
                                <li><a href="{{ route('settings.general') }}" class="{{ request()->routeIs('settings.general') ? 'active' : '' }}">General Settings</a></li>
                            </ul>
                        </li>

                    </ul>
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- Flash Notification Messages -->
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-block-helper me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{ $slot }}

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            &copy; {{ date('Y') }} {{ $companySetting->company_name ?? 'Small Business ERP' }}.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Small Business Accounting & Inventory ERP
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <!-- Pace js -->
    <script src="{{ asset('assets/libs/pace-js/pace.min.js') }}"></script>

    <script src="{{ asset('assets/js/app.js') }}"></script>

    @livewireScripts

    <!-- PWA Service Worker & Install Script -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('PWA ServiceWorker registered with scope:', registration.scope);
                }, function(err) {
                    console.log('PWA ServiceWorker registration failed:', err);
                });
            });
        }

        let deferredPrompt;
        const pwaInstallBtn = document.getElementById('pwa-install-btn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (pwaInstallBtn) {
                pwaInstallBtn.classList.remove('d-none');
                pwaInstallBtn.classList.add('d-inline-flex');
            }
        });

        if (pwaInstallBtn) {
            pwaInstallBtn.addEventListener('click', async () => {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`PWA install prompt outcome: ${outcome}`);
                deferredPrompt = null;
                pwaInstallBtn.classList.remove('d-inline-flex');
                pwaInstallBtn.classList.add('d-none');
            });
        }

        window.addEventListener('appinstalled', () => {
            console.log('PWA installed successfully');
            if (pwaInstallBtn) {
                pwaInstallBtn.classList.remove('d-inline-flex');
                pwaInstallBtn.classList.add('d-none');
            }
        });
    </script>
</body>

</html>
