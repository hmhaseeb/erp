<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Stock Valuation & Inventory Report</h4>
                    <p class="text-muted font-size-13 mb-0">Total asset value of products in stock using weighted average cost accounting.</p>
                </div>
                <div class="page-title-right">
                    <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
                        <i class="bx bx-printer me-1"></i> Print Stock Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Total Stock Asset Value</span>
                    <h4 class="mb-0 text-primary fw-bold">AED {{ number_format($totalValuation, 2) }}</h4>
                    <small class="text-muted">Weighted Average Cost</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Total Physical Units</span>
                    <h4 class="mb-0 text-success fw-bold">{{ number_format($totalStockQty, 2) }}</h4>
                    <small class="text-muted">Total inventory count</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Active Catalog SKUs</span>
                    <h4 class="mb-0 text-dark fw-bold">{{ number_format($totalItems) }}</h4>
                    <small class="text-muted">Product Variations</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted font-size-12 d-block mb-1">Low Stock Alerts</span>
                    <h4 class="mb-0 text-danger fw-bold">{{ number_format($lowStockCount) }}</h4>
                    <small class="text-muted">At or below minimum stock</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Products</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by name, SKU, barcode, brand...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Filter Category</label>
                    <select wire:model.live="category_id_filter" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Stock Status</label>
                    <select wire:model.live="stock_status_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock (At Min)</option>
                        <option value="out_of_stock">Out of Stock (0)</option>
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
                <div class="col-lg-2 col-md-4">
                    <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                        <i class="bx bx-reset me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Valuation Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, category_id_filter, stock_status_filter, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Calculating inventory valuation...</span>
            </div>

            <div wire:loading.remove wire:target="search, category_id_filter, stock_status_filter, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('product_code')" class="sortable" style="width: 120px;">
                                SKU / Code
                                @if($sortField === 'product_code')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('name')" class="sortable">
                                Product Title
                                @if($sortField === 'name')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Category</th>
                            <th wire:click="sortBy('current_stock')" class="sortable text-end">
                                Current Stock
                                @if($sortField === 'current_stock')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('weighted_cost')" class="sortable text-end">
                                Avg Cost (AED)
                                @if($sortField === 'weighted_cost')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('sales_price')" class="sortable text-end">
                                Retail Price (AED)
                                @if($sortField === 'sales_price')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th class="text-end">Total Asset Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                            <tr>
                                <td><code>{{ $p->product_code }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($p->image)
                                            <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" class="rounded me-2 object-fit-cover border" style="width: 32px; height: 32px; min-width: 32px;">
                                        @else
                                            <div class="rounded me-2 bg-light text-primary d-inline-flex align-items-center justify-content-center border" style="width: 32px; height: 32px; min-width: 32px;">
                                                <i class="bx bx-package font-size-16"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="fw-semibold text-dark">{{ $p->name }}</span>
                                            @if($p->barcode) <small class="text-muted d-block"><i class="bx bx-barcode"></i> {{ $p->barcode }}</small> @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $p->category->name ?? '-' }}</td>
                                <td class="text-end font-monospace">
                                    <span class="badge {{ $p->current_stock <= $p->min_stock ? 'badge-soft-warning' : 'badge-soft-success' }} font-size-12">
                                        {{ number_format($p->current_stock, 2) }} {{ $p->unit->name ?? '' }}
                                    </span>
                                </td>
                                <td class="text-end text-muted font-monospace">AED {{ number_format($p->weighted_cost, 2) }}</td>
                                <td class="text-end text-success font-monospace">AED {{ number_format($p->sales_price, 2) }}</td>
                                <td class="text-end fw-bold text-primary font-monospace font-size-14">
                                    AED {{ number_format($p->current_stock * $p->weighted_cost, 2) }}
                                </td>
                                <td>
                                    @if($p->current_stock <= 0)
                                        <span class="badge badge-soft-danger">Out of Stock</span>
                                    @elseif($p->current_stock <= $p->min_stock)
                                        <span class="badge badge-soft-warning">Low Stock Alert</span>
                                    @else
                                        <span class="badge badge-soft-success">In Stock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-package"></i>
                                        </div>
                                        <h6 class="text-dark">No products found</h6>
                                        <p class="text-muted font-size-13 mb-3">No inventory items match your filter criteria.</p>
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
                    Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} records
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
