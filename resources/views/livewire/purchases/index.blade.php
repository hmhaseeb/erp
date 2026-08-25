<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Purchase Invoices</h4>
                    <p class="text-muted font-size-13 mb-0">Record vendor bills, goods received, payment terms, and inventory updates.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('purchases.create') }}" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-plus me-1"></i> New Purchase Invoice
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
                    <label class="form-label font-size-12 text-muted mb-1">Search Purchases</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by Purchase #, supplier, ref...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Filter Supplier</label>
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
                        <option value="Bank">Bank Transfer</option>
                        <option value="Credit">Credit (Payable)</option>
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

    <!-- Purchases Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, supplier_id_filter, payment_type_filter, status_filter, date_from, date_to, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading purchase invoices...</span>
            </div>

            <div wire:loading.remove wire:target="search, supplier_id_filter, payment_type_filter, status_filter, date_from, date_to, perPage, sortBy, resetFilters" class="table-responsive">
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
                            <th wire:click="sortBy('grand_total')" class="sortable text-end">
                                Grand Total
                                @if($sortField === 'grand_total')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th class="text-end">Paid Amount</th>
                            <th class="text-end">Due Amount</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $pur)
                            <tr>
                                <td><code>{{ $pur->purchase_number }}</code></td>
                                <td>{{ $pur->purchase_date }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $pur->supplier->name ?? 'Unknown Supplier' }}</span>
                                    @if($pur->supplier && $pur->supplier->company_name)
                                        <small class="text-muted d-block">{{ $pur->supplier->company_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $pur->payment_type === 'Cash' ? 'badge-soft-success' : ($pur->payment_type === 'Bank' ? 'badge-soft-info' : 'badge-soft-warning') }}">
                                        {{ $pur->payment_type }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-dark">AED {{ number_format($pur->grand_total, 2) }}</td>
                                <td class="text-end text-success">AED {{ number_format($pur->paid_amount, 2) }}</td>
                                <td class="text-end font-monospace {{ $pur->due_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    AED {{ number_format($pur->due_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge {{ $pur->status === 'Confirmed' ? 'badge-soft-success' : ($pur->status === 'Cancelled' ? 'badge-soft-danger' : 'badge-soft-warning') }}">
                                        {{ $pur->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="viewDetails({{ $pur->id }})" class="btn btn-sm btn-outline-primary" title="View Purchase Details">
                                        <i class="bx bx-show me-1"></i> View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-shopping-bag"></i>
                                        </div>
                                        @if($search || $supplier_id_filter || $payment_type_filter || $date_from || $date_to)
                                            <h6 class="text-dark">No purchase invoices found</h6>
                                            <p class="text-muted font-size-13 mb-3">No purchase orders match your filter criteria.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Reset Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No purchase invoices created yet</h6>
                                            <p class="text-muted font-size-13 mb-3">Create your first purchase invoice to receive inventory stock and record payables.</p>
                                            <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> New Purchase Invoice
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
                    Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ $purchases->total() }} records
                </div>
                <div>
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Details Modal -->
    @if($selectedPurchase)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Purchase Invoice #{{ $selectedPurchase->purchase_number }}</h5>
                        <button type="button" class="btn-close" wire:click="closeDetails"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Supplier:</strong> {{ $selectedPurchase->supplier->name ?? '-' }}</p>
                                @if($selectedPurchase->supplier && $selectedPurchase->supplier->company_name)
                                    <p class="mb-1 text-muted"><strong>Company:</strong> {{ $selectedPurchase->supplier->company_name }}</p>
                                @endif
                                <p class="mb-1"><strong>Date:</strong> {{ $selectedPurchase->purchase_date }}</p>
                                <p class="mb-0"><strong>Payment Method:</strong> {{ $selectedPurchase->payment_type }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-1">
                                    <strong>Status:</strong>
                                    <span class="badge {{ $selectedPurchase->status === 'Confirmed' ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                        {{ $selectedPurchase->status }}
                                    </span>
                                </p>
                                @if($selectedPurchase->reference_number)
                                    <p class="mb-1 text-muted"><strong>Supplier Bill Ref:</strong> {{ $selectedPurchase->reference_number }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm font-size-13">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Unit Cost</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">VAT</th>
                                        <th class="text-end">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedPurchase->items as $item)
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
                                <p class="font-size-13">{{ $selectedPurchase->notes ?? 'None' }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-1 font-size-13">Subtotal: <strong>AED {{ number_format($selectedPurchase->subtotal, 2) }}</strong></p>
                                <p class="mb-1 font-size-13">VAT Amount: <strong>AED {{ number_format($selectedPurchase->vat_amount, 2) }}</strong></p>
                                @if($selectedPurchase->discount_amount > 0)
                                    <p class="mb-1 font-size-13 text-danger">Discount: <strong>- AED {{ number_format($selectedPurchase->discount_amount, 2) }}</strong></p>
                                @endif
                                <h4 class="text-primary fw-bold">Grand Total: AED {{ number_format($selectedPurchase->grand_total, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @if($selectedPurchase->status === 'Confirmed')
                            <button type="button" onclick="confirm('Are you sure you want to cancel this purchase invoice? Stock will be removed from inventory and accounting entries reversed.') || event.stopImmediatePropagation()" wire:click="cancelPurchase({{ $selectedPurchase->id }})" class="btn btn-danger me-auto">
                                <i class="bx bx-x-circle me-1"></i> Cancel Purchase (Reverse)
                            </button>
                        @endif
                        <button type="button" class="btn btn-light" wire:click="closeDetails">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
