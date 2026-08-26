<div>
    <!-- Page Header -->
    <x-page-header title="Product Catalog & Inventory" subtitle="Manage product items, pictures, prices, barcodes, and stock levels.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
            <i class="bx bx-plus me-1"></i> Register New Product
        </button>
    </x-page-header>

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
            <label class="form-label font-size-12 text-muted mb-1">Filter by Category</label>
            <x-searchable-select wire:model.live="category_id_filter" class="form-select" placeholder="All Categories">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Stock Status</label>
            <x-searchable-select wire:model.live="stock_status_filter" class="form-select" placeholder="All Stock Status">
                <option value="">All Stock Status</option>
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock Alert</option>
                <option value="out_of_stock">Out of Stock</option>
            </x-searchable-select>
        </div>
        <div class="col-lg-1 col-md-2">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="10">10</option>
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

    <!-- Data Table Card -->
    <x-table-card target="search, category_id_filter, stock_status_filter, perPage, sortBy, resetFilters" loadingText="Loading records..." :paginator="$products">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="product_code" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">SKU / Code</x-th-sort>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection">Product Details</x-th-sort>
                    <th>Category</th>
                    <th>Unit</th>
                    <x-th-sort field="purchase_price" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Purchase Cost</x-th-sort>
                    <x-th-sort field="sales_price" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Retail Price</x-th-sort>
                    <x-th-sort field="weighted_cost" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Avg Cost</x-th-sort>
                    <x-th-sort field="current_stock" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Current Stock</x-th-sort>
                    <th>Status</th>
                    <th class="text-center" style="width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td><code>{{ $p->product_code }}</code></td>
                        <td>
                            <div class="d-flex align-items-center cursor-pointer" wire:click="showProductDetails({{ $p->id }})" title="Click to view details">
                                @if($p->image)
                                    <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" class="rounded me-2 object-fit-cover border" style="width: 36px; height: 36px; min-width: 36px;">
                                @else
                                    <div class="rounded me-2 bg-light text-primary d-inline-flex align-items-center justify-content-center border" style="width: 36px; height: 36px; min-width: 36px;">
                                        <i class="bx bx-package font-size-18"></i>
                                    </div>
                                @endif
                                <div>
                                    <span class="fw-semibold text-dark d-block hover-primary">{{ $p->name }}</span>
                                    <div class="d-flex gap-2">
                                        @if($p->brand) <small class="text-muted"><i class="bx bx-tag font-size-11"></i> {{ $p->brand }}</small> @endif
                                        @if($p->barcode) <small class="text-muted"><i class="bx bx-barcode font-size-11"></i> {{ $p->barcode }}</small> @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $p->category->name ?? '-' }}</td>
                        <td><x-badge type="secondary">{{ $p->unit->name ?? '-' }}</x-badge></td>
                        <td class="text-end text-muted font-monospace">AED {{ number_format($p->purchase_price, 2) }}</td>
                        <td class="text-end fw-bold text-success font-monospace">AED {{ number_format($p->sales_price, 2) }}</td>
                        <td class="text-end text-muted font-monospace">AED {{ number_format($p->weighted_cost, 2) }}</td>
                        <td class="text-end font-monospace">
                            @if($p->current_stock <= 0)
                                <x-badge type="danger">0.00 (Out of Stock)</x-badge>
                            @elseif($p->current_stock <= $p->min_stock)
                                <x-badge type="warning">{{ number_format($p->current_stock, 2) }} (Low)</x-badge>
                            @else
                                <x-badge type="success">{{ number_format($p->current_stock, 2) }}</x-badge>
                            @endif
                        </td>
                        <td>
                            <x-badge :type="$p->status ? 'success' : 'secondary'">
                                {{ $p->status ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </td>
                        <td class="text-center">
                            <button wire:click="showProductDetails({{ $p->id }})" class="btn btn-sm btn-outline-info" title="View Product Details">
                                <i class="bx bx-show"></i>
                            </button>
                            <button wire:click="editProduct({{ $p->id }})" class="btn btn-sm btn-outline-primary ms-1" title="Edit Product">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <button onclick="confirm('Are you sure you want to delete this product?') || event.stopImmediatePropagation()" wire:click="deleteProduct({{ $p->id }})" class="btn btn-sm btn-outline-danger ms-1" title="Delete Product">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <x-empty-state 
                                icon="bx bx-package" 
                                title="No products registered yet" 
                                message="Register your first product item to begin tracking stock, sales, and purchases."
                                :search="$search || $category_id_filter || $stock_status_filter"
                                addAction="openModal"
                                addLabel="Register Product" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Modals -->
    @include('livewire.products.partials.product-view-modal')
    @include('livewire.products.partials.product-modal')
</div>
