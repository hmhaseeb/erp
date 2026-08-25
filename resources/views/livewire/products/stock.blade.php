<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Stock Movements & Audit Log</h4>
                    <p class="text-muted font-size-13 mb-0">Track all inventory movements, purchases, sales deductions, and adjustments.</p>
                </div>
                <div class="page-title-right">
                    <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-slider-alt me-1"></i> Record Stock Adjustment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Movements</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search product, SKU, note...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Movement Type</label>
                    <select wire:model.live="movement_type_filter" class="form-select">
                        <option value="">All Types</option>
                        <option value="OPENING">OPENING</option>
                        <option value="PURCHASE">PURCHASE</option>
                        <option value="SALE">SALE</option>
                        <option value="PURCHASE_RETURN">PURCHASE RETURN</option>
                        <option value="SALES_RETURN">SALES RETURN</option>
                        <option value="ADJUSTMENT_IN">ADJUSTMENT IN (+)</option>
                        <option value="ADJUSTMENT_OUT">ADJUSTMENT OUT (-)</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Filter Product</label>
                    <select wire:model.live="product_id_filter" class="form-select">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
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

    <!-- Stock Movements Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, movement_type_filter, product_id_filter, date_from, date_to, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading movements log...</span>
            </div>

            <div wire:loading.remove wire:target="search, movement_type_filter, product_id_filter, date_from, date_to, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('date')" class="sortable" style="width: 110px;">
                                Date
                                @if($sortField === 'date')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Product</th>
                            <th>Type</th>
                            <th class="text-end text-success">Qty In (+)</th>
                            <th class="text-end text-danger">Qty Out (-)</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Total Valuation</th>
                            <th>Reference / Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $m)
                            <tr>
                                <td>{{ $m->date }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($m->product && $m->product->image)
                                            <img src="{{ asset('storage/' . $m->product->image) }}" alt="{{ $m->product->name }}" class="rounded me-2 object-fit-cover border" style="width: 32px; height: 32px; min-width: 32px;">
                                        @else
                                            <div class="rounded me-2 bg-light text-primary d-inline-flex align-items-center justify-content-center border" style="width: 32px; height: 32px; min-width: 32px;">
                                                <i class="bx bx-package font-size-16"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="fw-semibold text-dark d-block">{{ $m->product->name ?? 'Product Deleted' }}</span>
                                            <small class="text-muted">{{ $m->product->product_code ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($m->movement_type) {
                                            'OPENING' => 'badge-soft-secondary',
                                            'PURCHASE' => 'badge-soft-primary',
                                            'SALE' => 'badge-soft-info',
                                            'PURCHASE_RETURN' => 'badge-soft-warning',
                                            'SALES_RETURN' => 'badge-soft-success',
                                            'ADJUSTMENT_IN' => 'badge-soft-success',
                                            'ADJUSTMENT_OUT' => 'badge-soft-danger',
                                            default => 'badge-soft-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} font-size-12">{{ $m->movement_type }}</span>
                                </td>
                                <td class="text-end font-monospace {{ $m->quantity_in > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                                    {{ $m->quantity_in > 0 ? '+' . number_format($m->quantity_in, 2) : '-' }}
                                </td>
                                <td class="text-end font-monospace {{ $m->quantity_out > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    {{ $m->quantity_out > 0 ? '-' . number_format($m->quantity_out, 2) : '-' }}
                                </td>
                                <td class="text-end text-muted">AED {{ number_format($m->unit_cost, 2) }}</td>
                                <td class="text-end fw-bold text-dark">
                                    AED {{ number_format(($m->quantity_in + $m->quantity_out) * $m->unit_cost, 2) }}
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $m->notes }}">
                                        {{ $m->notes ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-transfer"></i>
                                        </div>
                                        @if($search || $movement_type_filter || $product_id_filter || $date_from || $date_to)
                                            <h6 class="text-dark">No stock movements found</h6>
                                            <p class="text-muted font-size-13 mb-3">No stock movement logs match your filters.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Reset Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No stock movements logged yet</h6>
                                            <p class="text-muted font-size-13 mb-3">Stock movements will be recorded automatically during purchases, sales, and manual adjustments.</p>
                                            <button wire:click="openModal" class="btn btn-sm btn-primary">
                                                <i class="bx bx-slider-alt me-1"></i> Record Adjustment
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
                    Showing {{ $movements->firstItem() ?? 0 }} to {{ $movements->lastItem() ?? 0 }} of {{ $movements->total() }} records
                </div>
                <div>
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    @if($isModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Stock Adjustment</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveAdjustment">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Adjustment Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select Product <span class="text-danger">*</span></label>
                                <select wire:model.live="product_id" class="form-select @error('product_id') is-invalid @enderror">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} (Current Stock: {{ number_format($p->current_stock, 2) }})</option>
                                    @endforeach
                                </select>
                                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                                    <select wire:model="movement_type" class="form-select">
                                        <option value="ADJUSTMENT_IN">Stock In (+) / Increase</option>
                                        <option value="ADJUSTMENT_OUT">Stock Out (-) / Damaged / Decrease</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model="quantity" class="form-control @error('quantity') is-invalid @enderror">
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Unit Cost Price (AED) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" wire:model="unit_cost" class="form-control @error('unit_cost') is-invalid @enderror">
                                @error('unit_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Reason / Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="2" placeholder="e.g. Physical inventory count correction, damaged goods write-off"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="saveAdjustment">Apply Adjustment</span>
                                <span wire:loading wire:target="saveAdjustment"><i class="bx bx-loader-alt bx-spin me-1"></i> Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
