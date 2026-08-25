<div>
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Dashboard Overview</h4>
                <div class="page-title-right">
                    <span class="badge badge-soft-primary font-size-13 py-2 px-3">
                        <i class="bx bx-calendar me-1"></i> {{ date('l, d F Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Top KPI Cards Row 1 -->
    <div class="row">
        <!-- Today Sales -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate font-size-13">Today Sales</span>
                            <h4 class="mb-0 text-success fw-bold">
                                AED {{ number_format($todaySales, 2) }}
                            </h4>
                        </div>
                        <div class="col-6 text-end">
                            <div class="avatar-sm ms-auto">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle font-size-24">
                                    <i class="bx bx-shopping-bag"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today Purchases -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate font-size-13">Today Purchases</span>
                            <h4 class="mb-0 text-primary fw-bold">
                                AED {{ number_format($todayPurchases, 2) }}
                            </h4>
                        </div>
                        <div class="col-6 text-end">
                            <div class="avatar-sm ms-auto">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-24">
                                    <i class="bx bx-cart"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today Income -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate font-size-13">Today Income</span>
                            <h4 class="mb-0 text-info fw-bold">
                                AED {{ number_format($todayIncome, 2) }}
                            </h4>
                        </div>
                        <div class="col-6 text-end">
                            <div class="avatar-sm ms-auto">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle font-size-24">
                                    <i class="bx bx-trending-up"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today Expenses -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate font-size-13">Today Expenses</span>
                            <h4 class="mb-0 text-danger fw-bold">
                                AED {{ number_format($todayExpense, 2) }}
                            </h4>
                        </div>
                        <div class="col-6 text-end">
                            <div class="avatar-sm ms-auto">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle font-size-24">
                                    <i class="bx bx-trending-down"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row 2 (Balances & Inventory) -->
    <div class="row">
        <!-- Cash Balance -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted mb-2 d-block font-size-13">Cash Balance</span>
                    <h4 class="mb-0 text-dark fw-bold">AED {{ number_format($cashBalance, 2) }}</h4>
                </div>
            </div>
        </div>

        <!-- Bank Balance -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted mb-2 d-block font-size-13">Bank Balance</span>
                    <h4 class="mb-0 text-dark fw-bold">AED {{ number_format($bankBalance, 2) }}</h4>
                </div>
            </div>
        </div>

        <!-- Receivables -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted mb-2 d-block font-size-13">Customer Receivables</span>
                    <h4 class="mb-0 text-warning fw-bold">AED {{ number_format($receivables, 2) }}</h4>
                </div>
            </div>
        </div>

        <!-- Payables -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted mb-2 d-block font-size-13">Supplier Payables</span>
                    <h4 class="mb-0 text-danger fw-bold">AED {{ number_format($payables, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Valuation Summary & Low Stock -->
    <div class="row mt-3">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0">Stock Valuation</h5>
                </div>
                <div class="card-body text-center py-4">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-24">
                            <i class="bx bx-package"></i>
                        </span>
                    </div>
                    <p class="text-muted mb-1 font-size-13">Total Inventory Asset Value</p>
                    <h3 class="text-primary fw-bold mb-0">AED {{ number_format($stockValue, 2) }}</h3>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-danger"><i class="bx bx-error align-middle me-1"></i> Low Stock Alert</h5>
                    <span class="badge bg-danger">{{ $lowStockProducts->count() }} Products</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 250px;">
                        <table class="table align-middle table-nowrap mb-0 font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Current Stock</th>
                                    <th class="text-end">Min Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts as $prod)
                                    <tr>
                                        <td>
                                            <span class="fw-medium">{{ $prod->name }}</span>
                                            <small class="text-muted d-block">{{ $prod->product_code }}</small>
                                        </td>
                                        <td class="text-end text-danger fw-bold">{{ number_format($prod->current_stock, 2) }}</td>
                                        <td class="text-end text-muted">{{ number_format($prod->min_stock, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">All products have sufficient stock.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales & Purchases -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Sales Invoices</h5>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th class="text-end">Grand Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td><a href="{{ route('sales.index') }}" class="fw-bold text-primary">{{ $sale->invoice_number }}</a></td>
                                        <td>{{ $sale->sale_date }}</td>
                                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                        <td><span class="badge badge-soft-info">{{ $sale->payment_type }}</span></td>
                                        <td class="text-end fw-bold text-dark">AED {{ number_format($sale->grand_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No sales invoices recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Purchase Invoices</h5>
                    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Purchase #</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Type</th>
                                    <th class="text-end">Grand Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPurchases as $pur)
                                    <tr>
                                        <td><a href="{{ route('purchases.index') }}" class="fw-bold text-primary">{{ $pur->purchase_number }}</a></td>
                                        <td>{{ $pur->purchase_date }}</td>
                                        <td>{{ $pur->supplier->name ?? 'N/A' }}</td>
                                        <td><span class="badge badge-soft-primary">{{ $pur->payment_type }}</span></td>
                                        <td class="text-end fw-bold text-dark">AED {{ number_format($pur->grand_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No purchase invoices recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
