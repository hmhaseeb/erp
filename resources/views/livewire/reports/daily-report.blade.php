<div>
    <!-- Page Header -->
    <x-page-header title="Daily Business Executive Summary" subtitle="Daily financial snapshot of sales, purchases, income, expenses, and cash/bank balances.">
        <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-printer me-1"></i> Print Daily Report
        </button>
    </x-page-header>

    <!-- Date Selection Card -->
    <x-filter-card>
        <div class="col-12 col-md-4">
            <label class="form-label font-size-12 text-muted mb-1">Select Report Date</label>
            <input type="date" wire:model.live="date" class="form-control">
        </div>
        <div class="col-12 col-md-8 text-md-end mt-3 mt-md-0">
            <x-badge type="primary" size="font-size-13 py-2 px-3">
                <i class="bx bx-calendar me-1"></i> Selected: {{ date('l, d F Y', strtotime($date)) }}
            </x-badge>
        </div>
    </x-filter-card>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Today's Net Sales" 
                :amount="$report['sales']['net']" 
                prefix="AED " 
                color="success" 
                :subtitle="$report['sales']['count'] . ' Invoices Issued'" 
                icon="bx-shopping-bag" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Today's Net Purchases" 
                :amount="$report['purchases']['net']" 
                prefix="AED " 
                color="primary" 
                :subtitle="$report['purchases']['count'] . ' Bills Received'" 
                icon="bx-cart" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Today's Other Income" 
                :amount="$report['income']" 
                prefix="AED " 
                color="info" 
                :subtitle="$incomes->count() . ' Transactions'" 
                icon="bx-trending-up" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Today's Expenses" 
                :amount="$report['expense']" 
                prefix="AED " 
                color="danger" 
                :subtitle="$expenses->count() . ' Entries'" 
                icon="bx-trending-down" />
        </div>
    </div>

    <!-- Sales & Purchases Details Row -->
    <div class="row">
        <!-- Sales Breakdown -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Today's Sales Invoices</h5>
                    <x-badge type="success" size="font-size-12">Total: AED {{ number_format($report['sales']['gross'], 2) }}</x-badge>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 280px;">
                        <table class="table align-middle table-sm table-nowrap mb-0 font-size-12">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $s)
                                    <tr>
                                        <td><code>{{ $s->invoice_number }}</code></td>
                                        <td>{{ $s->customer->name ?? 'Walk-in' }}</td>
                                        <td><x-badge type="info">{{ $s->payment_type }}</x-badge></td>
                                        <td class="text-end fw-bold text-success">AED {{ number_format($s->grand_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No sales invoices recorded for this date.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchases Breakdown -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Today's Purchase Bills</h5>
                    <x-badge type="primary" size="font-size-12">Total: AED {{ number_format($report['purchases']['gross'], 2) }}</x-badge>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 280px;">
                        <table class="table align-middle table-sm table-nowrap mb-0 font-size-12">
                            <thead class="table-light">
                                <tr>
                                    <th>Purchase #</th>
                                    <th>Supplier</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $p)
                                    <tr>
                                        <td><code>{{ $p->purchase_number }}</code></td>
                                        <td>{{ $p->supplier->name ?? '-' }}</td>
                                        <td><x-badge type="primary">{{ $p->payment_type }}</x-badge></td>
                                        <td class="text-end fw-bold text-dark">AED {{ number_format($p->grand_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No purchases recorded for this date.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income & Expenses Row -->
    <div class="row">
        <!-- Income Breakdown -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Today's Income Transactions</h5>
                    <x-badge type="info" size="font-size-12">Total: AED {{ number_format($report['income'], 2) }}</x-badge>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 240px;">
                        <table class="table align-middle table-sm table-nowrap mb-0 font-size-12">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Account</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incomes as $inc)
                                    <tr>
                                        <td><x-badge type="success">{{ $inc->category->name ?? 'Income' }}</x-badge></td>
                                        <td>{{ $inc->description ?? '-' }}</td>
                                        <td>{{ $inc->account->name ?? '-' }}</td>
                                        <td class="text-end fw-bold text-success">AED {{ number_format($inc->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No income transactions for this date.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Breakdown -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Today's Expenses</h5>
                    <x-badge type="danger" size="font-size-12">Total: AED {{ number_format($report['expense'], 2) }}</x-badge>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 240px;">
                        <table class="table align-middle table-sm table-nowrap mb-0 font-size-12">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Account</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $exp)
                                    <tr>
                                        <td><x-badge type="danger">{{ $exp->category->name ?? 'Expense' }}</x-badge></td>
                                        <td>{{ $exp->description ?? '-' }}</td>
                                        <td>{{ $exp->account->name ?? '-' }}</td>
                                        <td class="text-end fw-bold text-danger">AED {{ number_format($exp->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No expenses recorded for this date.</td>
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
