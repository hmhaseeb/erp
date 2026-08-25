<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Sales Statement & Tax Report</h4>
                    <p class="text-muted font-size-13 mb-0">Detailed sales register, VAT tax collections, customer breakdowns, and invoice statements.</p>
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
                    <span class="text-muted font-size-12 d-block mb-1">Total Sales Revenue</span>
                    <h4 class="mb-0 text-success fw-bold">AED {{ number_format($totalSales, 2) }}</h4>
                    <small class="text-muted">In period ({{ $start_date }} to {{ $end_date }})</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">VAT Output Tax (5%)</span>
                    <h4 class="mb-0 text-info fw-bold">AED {{ number_format($totalVat, 2) }}</h4>
                    <small class="text-muted">Payable Tax Collected</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Invoices Issued</span>
                    <h4 class="mb-0 text-primary fw-bold">{{ number_format($totalInvoices) }}</h4>
                    <small class="text-muted">Confirmed Sales</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Average Invoice Value</span>
                    <h4 class="mb-0 text-dark fw-bold">AED {{ number_format($avgInvoice, 2) }}</h4>
                    <small class="text-muted">Per Transaction</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Sales</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search invoice #, customer...">
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
                    <label class="form-label font-size-12 text-muted mb-1">Customer</label>
                    <select wire:model.live="customer_id_filter" class="form-select">
                        <option value="">All Customers</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
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

    <!-- Sales Report Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, start_date, end_date, customer_id_filter, payment_type_filter, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Generating sales statement...</span>
            </div>

            <div wire:loading.remove wire:target="search, start_date, end_date, customer_id_filter, payment_type_filter, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('invoice_number')" class="sortable" style="width: 130px;">
                                Invoice #
                                @if($sortField === 'invoice_number')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('sale_date')" class="sortable" style="width: 110px;">
                                Date
                                @if($sortField === 'sale_date')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Customer</th>
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
                        @forelse($sales as $s)
                            <tr>
                                <td><code>{{ $s->invoice_number }}</code></td>
                                <td>{{ $s->sale_date }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $s->customer->name ?? 'Walk-in Customer' }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $s->payment_type === 'Cash' ? 'badge-soft-success' : ($s->payment_type === 'Bank' ? 'badge-soft-info' : 'badge-soft-warning') }}">
                                        {{ $s->payment_type }}
                                    </span>
                                </td>
                                <td class="text-end text-muted font-monospace">AED {{ number_format($s->subtotal, 2) }}</td>
                                <td class="text-end text-info font-monospace">AED {{ number_format($s->vat_amount, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace font-size-14">AED {{ number_format($s->grand_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-file-blank"></i>
                                        </div>
                                        <h6 class="text-dark">No sales invoices found</h6>
                                        <p class="text-muted font-size-13 mb-3">No sales records match your specified date range or filter criteria.</p>
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
                    Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} records
                </div>
                <div>
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
