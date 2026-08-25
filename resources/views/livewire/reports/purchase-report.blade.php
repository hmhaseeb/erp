<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Purchase Register & Expense Statement</h4>
                    <p class="text-muted font-size-13 mb-0">Detailed vendor purchase order logs, VAT input taxes, supplier breakdowns, and cost totals.</p>
                </div>
                <div class="page-title-right">
                    <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
                        <i class="bx bx-printer me-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Metrics -->
    <div class="row mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Total Purchases Spend</span>
                    <h4 class="mb-0 text-primary fw-bold">AED {{ number_format($totalPurchases, 2) }}</h4>
                    <small class="text-muted">In period ({{ $start_date }} to {{ $end_date }})</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">VAT Input Tax (5%)</span>
                    <h4 class="mb-0 text-info fw-bold">AED {{ number_format($totalVat, 2) }}</h4>
                    <small class="text-muted">Recoverable Input Tax</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Purchase Bills Received</span>
                    <h4 class="mb-0 text-dark fw-bold">{{ number_format($totalBills) }}</h4>
                    <small class="text-muted">Confirmed Vendor Invoices</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Average Purchase Order</span>
                    <h4 class="mb-0 text-muted fw-bold">AED {{ number_format($avgBill, 2) }}</h4>
                    <small class="text-muted">Per Order</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Purchases</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search purchase #, supplier...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">From Date</label>
                    <input type="date" wire:model.live="start_date" class="form-control">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">To Date</label>
                    <input type="date" wire:model.live="end_date" class="form-control">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Supplier</label>
                    <select wire:model.live="supplier_id_filter" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Payment Method</label>
                    <select wire:model.live="payment_type_filter" class="form-select">
                        <option value="">All Methods</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank">Bank</option>
                        <option value="Credit">Credit</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-2">
                    <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-end">
                    <button type="button" wire:click="resetFilters" class="btn btn-sm btn-light">
                        <i class="bx bx-reset me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Report Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, start_date, end_date, supplier_id_filter, payment_type_filter, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Generating purchase report...</span>
            </div>

            <div wire:loading.remove wire:target="search, start_date, end_date, supplier_id_filter, payment_type_filter, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('purchase_number')" class="sortable" style="width: 130px;">
                                Purchase #
                                @if($sortField === 'purchase_number')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('purchase_date')" class="sortable" style="width: 110px;">
                                Date
                                @if($sortField === 'purchase_date')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Supplier</th>
                            <th>Payment Type</th>
                            <th class="text-end">Subtotal (Excl. VAT)</th>
                            <th class="text-end text-info">VAT Amount (5%)</th>
                            <th wire:click="sortBy('grand_total')" class="sortable text-end">
                                Grand Total (AED)
                                @if($sortField === 'grand_total')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $p)
                            <tr>
                                <td><code>{{ $p->purchase_number }}</code></td>
                                <td>{{ $p->purchase_date }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $p->supplier->name ?? 'Supplier' }}</span>
                                    @if($p->supplier && $p->supplier->company_name)
                                        <small class="text-muted d-block">{{ $p->supplier->company_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $p->payment_type === 'Cash' ? 'badge-soft-success' : ($p->payment_type === 'Bank' ? 'badge-soft-info' : 'badge-soft-warning') }}">
                                        {{ $p->payment_type }}
                                    </span>
                                </td>
                                <td class="text-end text-muted font-monospace">AED {{ number_format($p->subtotal, 2) }}</td>
                                <td class="text-end text-info font-monospace">AED {{ number_format($p->vat_amount, 2) }}</td>
                                <td class="text-end fw-bold text-dark font-monospace font-size-14">AED {{ number_format($p->grand_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-file-blank"></i>
                                        </div>
                                        <h6 class="text-dark">No purchase invoices found</h6>
                                        <p class="text-muted font-size-13 mb-3">No purchase orders match your specified date range or filter criteria.</p>
                                        <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                            <i class="bx bx-reset me-1"></i> Reset Filters
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted font-size-13 mb-2 mb-sm-0">
                    Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ $purchases->total() }} records
                </div>
                <div>
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
