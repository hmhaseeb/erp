<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Purchase Returns / Debit Notes</h4>
                    <p class="text-muted font-size-13 mb-0">Record goods returned to suppliers, inventory deduction, and payable credit adjustments.</p>
                </div>
                <div class="page-title-right">
                    <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-plus me-1"></i> New Purchase Return
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Returns</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by Return #, supplier, reason...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Filter Supplier</label>
                    <select wire:model.live="supplier_id_filter" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
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
                    </select>
                </div>
                <div class="col-lg-1 col-md-2">
                    <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                        <i class="bx bx-reset"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Returns Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, supplier_id_filter, date_from, date_to, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading returns...</span>
            </div>

            <div wire:loading.remove wire:target="search, supplier_id_filter, date_from, date_to, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('return_number')" class="sortable" style="width: 130px;">
                                Return #
                                @if($sortField === 'return_number')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('return_date')" class="sortable" style="width: 110px;">
                                Date
                                @if($sortField === 'return_date')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Supplier</th>
                            <th>Items Returned</th>
                            <th wire:click="sortBy('grand_total')" class="sortable text-end">
                                Return Total
                                @if($sortField === 'grand_total')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Reason / Note</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $r)
                            <tr>
                                <td><code>{{ $r->return_number }}</code></td>
                                <td>{{ $r->return_date }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $r->supplier->name ?? 'Unknown' }}</span>
                                    @if($r->supplier && $r->supplier->company_name)
                                        <small class="text-muted d-block">{{ $r->supplier->company_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @foreach($r->items as $item)
                                        <div>{{ $item->product->name ?? 'Product' }} ({{ number_format($item->quantity, 2) }})</div>
                                    @endforeach
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    AED {{ number_format($r->grand_total, 2) }}
                                </td>
                                <td>{{ $r->return_reason ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-soft-success font-size-12">{{ $r->status ?? 'Confirmed' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-undo"></i>
                                        </div>
                                        @if($search || $supplier_id_filter || $date_from || $date_to)
                                            <h6 class="text-dark">No purchase returns found</h6>
                                            <p class="text-muted font-size-13 mb-3">No debit notes match your search filters.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Reset Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No purchase returns processed</h6>
                                            <p class="text-muted font-size-13 mb-3">Record purchase returns to debit supplier accounts and deduct returned items from stock.</p>
                                            <button wire:click="openModal" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> New Purchase Return
                                            </button>
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
                    Showing {{ $returns->firstItem() ?? 0 }} to {{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }} records
                </div>
                <div>
                    {{ $returns->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Return Modal Form -->
    @if($isModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Process Purchase Return (Debit Note)</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveReturn">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Return Number</label>
                                    <input type="text" wire:model="return_number" class="form-control" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Return Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="return_date" class="form-control @error('return_date') is-invalid @enderror">
                                    @error('return_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select wire:model="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }} (Balance: AED {{ number_format($s->current_balance, 2) }})</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Product to Return <span class="text-danger">*</span></label>
                                <select wire:model.live="product_id" class="form-select @error('product_id') is-invalid @enderror">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} (Stock: {{ number_format($p->current_stock, 2) }})</option>
                                    @endforeach
                                </select>
                                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Qty Returned <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model="quantity" class="form-control @error('quantity') is-invalid @enderror">
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cost Price (AED) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model="unit_price" class="form-control @error('unit_price') is-invalid @enderror">
                                    @error('unit_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">VAT %</label>
                                    <input type="number" step="0.01" wire:model="vat_percent" class="form-control">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Return Reason / Memo</label>
                                <textarea wire:model="return_reason" class="form-control" rows="2" placeholder="e.g. Defective merchandise, incorrect specifications"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <span wire:loading.remove wire:target="saveReturn">Process Return</span>
                                <span wire:loading wire:target="saveReturn"><i class="bx bx-loader-alt bx-spin me-1"></i> Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
