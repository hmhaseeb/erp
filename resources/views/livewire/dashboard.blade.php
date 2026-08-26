<div>
    <!-- Page Header -->
    <x-page-header title="Dashboard Overview" subtitle="Real-time key performance indicators, recent transactions, and inventory status.">
        <span class="badge bg-primary-subtle text-primary font-size-13 py-2 px-3 fw-medium">
            <i class="bx bx-calendar me-1"></i> {{ date('l, d F Y') }}
        </span>
    </x-page-header>

    <!-- Top KPI Cards Row 1: Operations -->
    <div class="row g-3">
        <!-- Today Sales -->
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Today Sales" 
                :amount="$todaySales" 
                prefix="AED " 
                color="success" 
                icon="bx bx-shopping-bag" />
        </div>

        <!-- Today Purchases -->
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Today Purchases" 
                :amount="$todayPurchases" 
                prefix="AED " 
                color="primary" 
                icon="bx bx-cart" />
        </div>

        <!-- Today Income -->
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Today Income" 
                :amount="$todayIncome" 
                prefix="AED " 
                color="info" 
                icon="bx bx-trending-up" />
        </div>

        <!-- Today Expenses -->
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Today Expenses" 
                :amount="$todayExpense" 
                prefix="AED " 
                color="danger" 
                icon="bx bx-trending-down" />
        </div>
    </div>

    <!-- KPI Cards Row 2: Balances & Receivables/Payables -->
    <div class="row g-3 mt-1">
        <!-- Cash Balance -->
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Cash In Hand" 
                :amount="$cashBalance" 
                prefix="AED " 
                color="dark" 
                icon="bx bx-wallet" />
        </div>

        <!-- Bank Balance -->
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Bank Balance" 
                :amount="$bankBalance" 
                prefix="AED " 
                color="primary" 
                icon="bx bx-buildings" />
        </div>

        <!-- Receivables -->
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Customer Receivables" 
                :amount="$receivables" 
                prefix="AED " 
                color="warning" 
                icon="bx bx-user-pin" />
        </div>

        <!-- Payables -->
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Supplier Payables" 
                :amount="$payables" 
                prefix="AED " 
                color="danger" 
                icon="bx bx-wallet-alt" />
        </div>
    </div>

    <!-- Stock Valuation Summary & Low Stock + Recent Activity -->
    <div class="row g-3 mt-2">
        <div class="col-xl-4 col-lg-5">
            <!-- Stock Valuation Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 font-size-15 fw-bold">Stock Valuation</h5>
                </div>
                <div class="card-body text-center py-4">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title rounded-circle font-size-24" style="background-color: rgba(85, 110, 230, 0.12); color: #556ee6;">
                            <i class="bx bx-package"></i>
                        </span>
                    </div>
                    <p class="text-muted mb-1 font-size-13">Total Inventory Asset Value</p>
                    <h3 class="text-primary fw-bold mb-0 font-monospace">AED {{ number_format($stockValue, 2) }}</h3>
                </div>
            </div>

            <!-- Low Stock Alert Card -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-danger font-size-14 fw-bold">
                        <i class="bx bx-error align-middle me-1"></i> Low Stock Alerts
                    </h5>
                    <x-badge type="danger">{{ $lowStockProducts->count() }} Items</x-badge>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 250px;">
                        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
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
                                            <span class="fw-semibold text-dark">{{ $prod->name }}</span>
                                            <small class="text-muted d-block font-monospace">{{ $prod->product_code }}</small>
                                        </td>
                                        <td class="text-end text-danger fw-bold font-monospace">{{ number_format($prod->current_stock, 2) }}</td>
                                        <td class="text-end text-muted font-monospace">{{ number_format($prod->min_stock, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="bx bx-check-circle text-success font-size-18 d-block mb-1"></i>
                                            All products have sufficient stock levels.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales & Purchases -->
        <div class="col-xl-8 col-lg-7">
            <!-- Recent Sales Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 font-size-15 fw-bold">Recent Sales Invoices</h5>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-link text-decoration-none">
                        View All <i class="bx bx-chevron-right align-middle"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Payment Type</th>
                                    <th class="text-end">Grand Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td>
                                            <a href="{{ route('sales.index') }}" class="fw-bold text-primary font-monospace">
                                                {{ $sale->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $sale->sale_date }}</td>
                                        <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                                        <td>
                                            <x-badge :type="$sale->payment_type === 'Cash' ? 'success' : ($sale->payment_type === 'Bank' ? 'info' : 'primary')">
                                                {{ $sale->payment_type ?? 'Credit' }}
                                            </x-badge>
                                        </td>
                                        <td class="text-end fw-bold font-monospace text-dark">
                                            AED {{ number_format($sale->grand_total, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bx bx-receipt font-size-20 d-block mb-1 text-muted"></i>
                                            No sales invoices recorded yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Purchases Card -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 font-size-15 fw-bold">Recent Purchase Invoices</h5>
                    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-link text-decoration-none">
                        View All <i class="bx bx-chevron-right align-middle"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Purchase #</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Payment Type</th>
                                    <th class="text-end">Grand Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPurchases as $pur)
                                    <tr>
                                        <td>
                                            <a href="{{ route('purchases.index') }}" class="fw-bold text-primary font-monospace">
                                                {{ $pur->purchase_number }}
                                            </a>
                                        </td>
                                        <td>{{ $pur->purchase_date }}</td>
                                        <td>{{ $pur->supplier->name ?? 'N/A' }}</td>
                                        <td>
                                            <x-badge :type="$pur->payment_type === 'Cash' ? 'success' : ($pur->payment_type === 'Bank' ? 'info' : 'primary')">
                                                {{ $pur->payment_type ?? 'Credit' }}
                                            </x-badge>
                                        </td>
                                        <td class="text-end fw-bold font-monospace text-dark">
                                            AED {{ number_format($pur->grand_total, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bx bx-cart font-size-20 d-block mb-1 text-muted"></i>
                                            No purchase invoices recorded yet.
                                        </td>
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
