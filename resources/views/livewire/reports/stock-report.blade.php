<div>
    <!-- Page Header -->
    <x-page-header title="Stock Valuation & Inventory Report" subtitle="Total asset value of products in stock using weighted average cost accounting.">
        <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
            <i class="bx bx-printer me-1"></i> Print Stock Report
        </button>
    </x-page-header>

    <!-- KPI Summary Row -->
    <div class="row mb-3">
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Total Stock Asset Value" 
                :amount="$totalValuation" 
                prefix="AED " 
                color="primary" 
                subtitle="Weighted Average Cost" 
                icon="bx-dollar-circle" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Total Physical Units" 
                :amount="number_format($totalStockQty, 2)" 
                prefix="" 
                color="success" 
                subtitle="Total inventory count" 
                icon="bx-layer" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Active Catalog SKUs" 
                :amount="number_format($totalItems)" 
                prefix="" 
                color="info" 
                subtitle="Product Variations" 
                icon="bx-package" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-kpi-card 
                title="Low Stock Alerts" 
                :amount="number_format($lowStockCount)" 
                prefix="" 
                color="danger" 
                subtitle="At or below minimum stock" 
                icon="bx-error" />
        </div>
    </div>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-4 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Products</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by name, SKU, barcode, brand...">
            </div>
        </div>
        <div class="col-lg-3 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Filter Category</label>
            <x-searchable-select wire:model.live="category_id_filter" class="form-select" placeholder="All Categories">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Stock Status</label>
            <x-searchable-select wire:model.live="stock_status_filter" class="form-select" placeholder="All Status">
                <option value="">All Status</option>
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock (At Min)</option>
                <option value="out_of_stock">Out of Stock (0)</option>
            </x-searchable-select>
        </div>
        <div class="col-lg-1 col-md-2">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </x-searchable-select>
        </div>
        <div class="col-lg-2 col-md-4">
            <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                <i class="bx bx-reset me-1"></i> Reset Filters
            </button>
        </div>
    </x-filter-card>

    <!-- Stock Valuation Table Card -->
    <x-table-card target="search, category_id_filter, stock_status_filter, perPage, sortBy, resetFilters" loadingText="Calculating inventory valuation..." :paginator="$products">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="product_code" :sortField="$sortField" :sortDirection="$sortDirection" width="120px">SKU / Code</x-th-sort>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection">Product Title</x-th-sort>
                    <th>Category</th>
                    <x-th-sort field="current_stock" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Current Stock</x-th-sort>
                    <x-th-sort field="weighted_cost" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Avg Cost (AED)</x-th-sort>
                    <x-th-sort field="sales_price" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Retail Price (AED)</x-th-sort>
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
                            <x-badge :type="$p->current_stock <= $p->min_stock ? 'warning' : 'success'" size="font-size-12">
                                {{ number_format($p->current_stock, 2) }} {{ $p->unit->name ?? '' }}
                            </x-badge>
                        </td>
                        <td class="text-end text-muted font-monospace">AED {{ number_format($p->weighted_cost, 2) }}</td>
                        <td class="text-end text-success font-monospace">AED {{ number_format($p->sales_price, 2) }}</td>
                        <td class="text-end fw-bold text-primary font-monospace font-size-14">
                            AED {{ number_format($p->current_stock * $p->weighted_cost, 2) }}
                        </td>
                        <td>
                            @if($p->current_stock <= 0)
                                <x-badge type="danger">Out of Stock</x-badge>
                            @elseif($p->current_stock <= $p->min_stock)
                                <x-badge type="warning">Low Stock Alert</x-badge>
                            @else
                                <x-badge type="success">In Stock</x-badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-empty-state 
                                icon="bx bx-package" 
                                title="No products found" 
                                message="No inventory items match your filter criteria."
                                :search="$search || $category_id_filter || $stock_status_filter" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>
</div>
