<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Sales Invoices</h4>
                    <p class="text-muted font-size-13 mb-0">Issue customer invoices, track collections, view tax statements, and generate PDF invoices.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('sales.create') }}" class="btn btn-success waves-effect waves-light">
                        <i class="bx bx-plus me-1"></i> New Sales Invoice
                    </a>
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by Invoice #, customer...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Filter Customer</label>
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
                        <option value="Bank">Bank / Card</option>
                        <option value="Credit">Credit (Receivable)</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">From Date</label>
                    <input type="date" wire:model.live="date_from" class="form-control">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">To Date</label>
                    <input type="date" wire:model.live="date_to" class="form-control">
                </div>
                <div class="col-lg-1 col-md-2">
                    <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
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

    <!-- Sales Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, customer_id_filter, payment_type_filter, status_filter, date_from, date_to, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading sales invoices...</span>
            </div>

            <div wire:loading.remove wire:target="search, customer_id_filter, payment_type_filter, status_filter, date_from, date_to, perPage, sortBy, resetFilters" class="table-responsive">
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
                            <th wire:click="sortBy('grand_total')" class="sortable text-end">
                                Grand Total
                                @if($sortField === 'grand_total')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th class="text-end">Paid Amount</th>
                            <th class="text-end">Due Balance</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td><code>{{ $sale->invoice_number }}</code></td>
                                <td>{{ $sale->sale_date }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $sale->customer->name ?? 'Walk-in Customer' }}</span>
                                    @if($sale->customer && $sale->customer->company_name)
                                        <small class="text-muted d-block">{{ $sale->customer->company_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $sale->payment_type === 'Cash' ? 'badge-soft-success' : ($sale->payment_type === 'Bank' ? 'badge-soft-info' : 'badge-soft-warning') }}">
                                        {{ $sale->payment_type }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-dark">AED {{ number_format($sale->grand_total, 2) }}</td>
                                <td class="text-end text-success">AED {{ number_format($sale->paid_amount, 2) }}</td>
                                <td class="text-end font-monospace {{ $sale->due_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    AED {{ number_format($sale->due_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge {{ $sale->status === 'Confirmed' ? 'badge-soft-success' : ($sale->status === 'Cancelled' ? 'badge-soft-danger' : 'badge-soft-warning') }}">
                                        {{ $sale->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="viewDetails({{ $sale->id }})" class="btn btn-sm btn-outline-primary" title="View Invoice">
                                        <i class="bx bx-show me-1"></i> View
                                    </button>
                                    <a href="{{ route('sales.pdf', ['id' => $sale->id]) }}" target="_blank" class="btn btn-sm btn-outline-danger ms-1" title="Download PDF">
                                        <i class="bx bxs-file-pdf"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-shopping-bag"></i>
                                        </div>
                                        @if($search || $customer_id_filter || $payment_type_filter || $date_from || $date_to)
                                            <h6 class="text-dark">No sales invoices found</h6>
                                            <p class="text-muted font-size-13 mb-3">No sales records match your filter criteria.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Reset Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No sales invoices issued yet</h6>
                                            <p class="text-muted font-size-13 mb-3">Create your first sales invoice to sell inventory, record revenue, and issue receipts.</p>
                                            <a href="{{ route('sales.create') }}" class="btn btn-sm btn-success">
                                                <i class="bx bx-plus me-1"></i> New Sales Invoice
                                            </a>
                                        @endif
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

    <!-- Sales Details Modal -->
    @if($selectedSale)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Sales Invoice #{{ $selectedSale->invoice_number }}</h5>
                        <button type="button" class="btn-close" wire:click="closeDetails"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Customer:</strong> {{ $selectedSale->customer->name ?? 'Walk-in Customer' }}</p>
                                @if($selectedSale->customer && $selectedSale->customer->company_name)
                                    <p class="mb-1 text-muted"><strong>Company:</strong> {{ $selectedSale->customer->company_name }}</p>
                                @endif
                                <p class="mb-1"><strong>Sale Date:</strong> {{ $selectedSale->sale_date }}</p>
                                <p class="mb-0"><strong>Payment Method:</strong> {{ $selectedSale->payment_type }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-1">
                                    <strong>Status:</strong>
                                    <span class="badge {{ $selectedSale->status === 'Confirmed' ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                        {{ $selectedSale->status }}
                                    </span>
                                </p>
                                <a href="{{ route('sales.pdf', ['id' => $selectedSale->id]) }}" target="_blank" class="btn btn-sm btn-danger mt-1">
                                    <i class="bx bxs-file-pdf me-1"></i> Print / Download PDF Invoice
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm font-size-13">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Selling Price</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">VAT</th>
                                        <th class="text-end">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedSale->items as $item)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">{{ $item->product->name ?? 'Product' }}</span>
                                                <small class="text-muted d-block">{{ $item->product->product_code ?? '' }}</small>
                                            </td>
                                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                            <td class="text-end">AED {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end">AED {{ number_format($item->discount_amount, 2) }}</td>
                                            <td class="text-end">AED {{ number_format($item->vat_amount, 2) }}</td>
                                            <td class="text-end fw-bold">AED {{ number_format($item->line_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Notes:</small>
                                <p class="font-size-13">{{ $selectedSale->notes ?? 'None' }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-1 font-size-13">Subtotal: <strong>AED {{ number_format($selectedSale->subtotal, 2) }}</strong></p>
                                <p class="mb-1 font-size-13">VAT Amount: <strong>AED {{ number_format($selectedSale->vat_amount, 2) }}</strong></p>
                                @if($selectedSale->discount_amount > 0)
                                    <p class="mb-1 font-size-13 text-danger">Discount: <strong>- AED {{ number_format($selectedSale->discount_amount, 2) }}</strong></p>
                                @endif
                                <h4 class="text-success fw-bold">Grand Total: AED {{ number_format($selectedSale->grand_total, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @if($selectedSale->status === 'Confirmed')
                            <button type="button" onclick="confirm('Cancel this sales invoice? Stock will be returned to inventory and transactions reversed.') || event.stopImmediatePropagation()" wire:click="cancelSale({{ $selectedSale->id }})" class="btn btn-danger me-auto">
                                <i class="bx bx-x-circle me-1"></i> Cancel Invoice (Reverse)
                            </button>
                        @endif
                        <button type="button" class="btn btn-light" wire:click="closeDetails">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
