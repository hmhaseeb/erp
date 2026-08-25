<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Daily Business Executive Summary</h4>
                    <p class="text-muted font-size-13 mb-0">Daily financial snapshot of sales, purchases, income, expenses, and cash/bank balances.</p>
                </div>
                <div class="page-title-right">
                    <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
                        <i class="bx bx-printer me-1"></i> Print Daily Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Selection Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="form-label font-size-12 text-muted mb-1">Select Report Date</label>
                    <input type="date" wire:model.live="date" class="form-control">
                </div>
                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                    <span class="badge badge-soft-primary font-size-13 py-2 px-3">
                        <i class="bx bx-calendar me-1"></i> Selected: {{ date('l, d F Y', strtotime($date)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Today's Net Sales</span>
                    <h4 class="mb-0 text-success fw-bold">AED {{ number_format($report['sales']['net'], 2) }}</h4>
                    <small class="text-muted">{{ $report['sales']['count'] }} Invoices Issued</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Today's Net Purchases</span>
                    <h4 class="mb-0 text-primary fw-bold">AED {{ number_format($report['purchases']['net'], 2) }}</h4>
                    <small class="text-muted">{{ $report['purchases']['count'] }} Bills Received</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Today's Other Income</span>
                    <h4 class="mb-0 text-info fw-bold">AED {{ number_format($report['income'], 2) }}</h4>
                    <small class="text-muted">{{ $incomes->count() }} Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Today's Expenses</span>
                    <h4 class="mb-0 text-danger fw-bold">AED {{ number_format($report['expense'], 2) }}</h4>
                    <small class="text-muted">{{ $expenses->count() }} Entries</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales & Purchases Details Row -->
    <div class="row">
        <!-- Sales Breakdown -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Today's Sales Invoices</h5>
                    <span class="badge badge-soft-success font-size-12">Total: AED {{ number_format($report['sales']['gross'], 2) }}</span>
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
                                        <td><span class="badge badge-soft-info">{{ $s->payment_type }}</span></td>
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
                    <span class="badge badge-soft-primary font-size-12">Total: AED {{ number_format($report['purchases']['gross'], 2) }}</span>
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
                                        <td><span class="badge badge-soft-primary">{{ $p->payment_type }}</span></td>
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
                    <span class="badge badge-soft-info font-size-12">Total: AED {{ number_format($report['income'], 2) }}</span>
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
                                        <td><span class="badge badge-soft-success">{{ $inc->category->name ?? 'Income' }}</span></td>
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
                    <span class="badge badge-soft-danger font-size-12">Total: AED {{ number_format($report['expense'], 2) }}</span>
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
                                        <td><span class="badge badge-soft-danger">{{ $exp->category->name ?? 'Expense' }}</span></td>
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
