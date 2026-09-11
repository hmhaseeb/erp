<div>
    <!-- Page Header -->
    <x-page-header title="Product Catalog & Inventory" subtitle="Manage product items, pictures, prices, barcodes, and stock levels.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-plus me-1"></i> Register New Product
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-4">
            <label class="form-label font-size-12 text-muted mb-1">Search Products</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by name, SKU, barcode, brand...">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Filter by Category</label>
            <x-searchable-select wire:model.live="category_id_filter" class="form-select" placeholder="All Categories">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Stock Status</label>
            <x-searchable-select wire:model.live="stock_status_filter" class="form-select" placeholder="All Stock Status">
                <option value="">All Stock Status</option>
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock Alert</option>
                <option value="out_of_stock">Out of Stock</option>
            </x-searchable-select>
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-1">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </x-searchable-select>
        </div>
        <!-- Mobile Sort Controls (Visible on mobile where desktop <th> headers are hidden) -->
        <div class="col-12 col-sm-6 d-md-none">
            <label class="form-label font-size-12 text-muted mb-1">Sort Products</label>
            <div class="input-group">
                <select wire:model.live="sortField" class="form-select font-size-13">
                    <option value="id">Latest Added</option>
                    <option value="name">Product Name</option>
                    <option value="product_code">SKU / Code</option>
                    <option value="current_stock">Current Stock</option>
                    <option value="sales_price">Retail Price</option>
                    <option value="purchase_price">Purchase Cost</option>
                    <option value="weighted_cost">Average Cost</option>
                </select>
                <button type="button" wire:click="sortBy('{{ $sortField }}')" class="btn btn-light border px-3" title="Toggle sort direction">
                    <i class="bx bx-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} font-size-16 text-primary"></i>
                </button>
            </div>
        </div>
        <x-slot:extra>
            <div class="col-12 text-sm-end text-center mt-1">
                <button type="button" wire:click="resetFilters" class="btn btn-sm btn-light">
                    <i class="bx bx-reset me-1"></i> Reset Filters
                </button>
            </div>
        </x-slot:extra>
    </x-filter-card>

    <!-- Data Table Card -->
    <x-table-card target="search, category_id_filter, stock_status_filter, perPage, sortBy, resetFilters" loadingText="Loading records..." :paginator="$products">
        <!-- 1. Desktop & Tablet Table (>= 768px): All 10 existing columns retained -->
        <table class="table align-middle table-hover mb-0 font-size-13 d-none d-md-table" style="min-width: 920px;">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="product_code" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">SKU / Code</x-th-sort>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection" style="min-width: 170px;">Product Details</x-th-sort>
                    <th style="min-width: 110px;">Category</th>
                    <th>Unit</th>
                    <x-th-sort field="purchase_price" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Purchase Cost</x-th-sort>
                    <x-th-sort field="sales_price" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Retail Price</x-th-sort>
                    <x-th-sort field="weighted_cost" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Avg Cost</x-th-sort>
                    <x-th-sort field="current_stock" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Current Stock</x-th-sort>
                    <th>Status</th>
                    <th class="text-center text-nowrap" style="min-width: 120px;">Actions</th>
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
                                    <span class="fw-semibold text-dark d-block hover-primary text-wrap" style="max-width: 220px;">{{ $p->name }}</span>
                                    <div class="d-flex gap-2">
                                        @if($p->brand) <small class="text-muted"><i class="bx bx-tag font-size-11"></i> {{ $p->brand }}</small> @endif
                                        @if($p->barcode) <small class="text-muted"><i class="bx bx-barcode font-size-11"></i> {{ $p->barcode }}</small> @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $p->category->name ?? '-' }}</td>
                        <td><x-badge type="secondary">{{ $p->unit->name ?? '-' }}</x-badge></td>
                        <td class="text-end text-muted font-monospace">{{ currency() }} {{ number_format($p->purchase_price, 2) }}</td>
                        <td class="text-end fw-bold text-success font-monospace">{{ currency() }} {{ number_format($p->sales_price, 2) }}</td>
                        <td class="text-end text-muted font-monospace">{{ currency() }} {{ number_format($p->weighted_cost, 2) }}</td>
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

        <!-- 2. Mobile Responsive Card / List View (< 768px: 320px, 375px, 390px, 430px) -->
        <div class="d-md-none p-2 p-sm-3 inventory-mobile-list" style="max-width: 100%; overflow-x: hidden;">
            @forelse($products as $p)
                <div class="inventory-mobile-card" x-data="{ expanded: false }">
                    <!-- Top Card Header: SKU / Code, Status & Stock Status -->
                    <div class="inventory-card-header">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <code class="fw-bold font-size-12 text-primary bg-light px-2 py-1 rounded border">{{ $p->product_code }}</code>
                            <x-badge :type="$p->status ? 'success' : 'secondary'">
                                {{ $p->status ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </div>
                        <div>
                            @if($p->current_stock <= 0)
                                <x-badge type="danger">Out of Stock</x-badge>
                            @elseif($p->current_stock <= $p->min_stock)
                                <x-badge type="warning">Low Stock ({{ number_format($p->current_stock, 2) }})</x-badge>
                            @else
                                <x-badge type="success">In Stock ({{ number_format($p->current_stock, 2) }})</x-badge>
                            @endif
                        </div>
                    </div>

                    <!-- Card Body: Thumbnail Image, Product Name, Category & Brand -->
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <div class="flex-shrink-0 cursor-pointer" wire:click="showProductDetails({{ $p->id }})" title="Click to view details">
                            @if($p->image)
                                <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" class="rounded object-fit-cover border" style="width: 44px; height: 44px; min-width: 44px;">
                            @else
                                <div class="rounded bg-light text-primary d-inline-flex align-items-center justify-content-center border" style="width: 44px; height: 44px; min-width: 44px;">
                                    <i class="bx bx-package font-size-20"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="inventory-card-title mb-1 cursor-pointer" wire:click="showProductDetails({{ $p->id }})">
                                {{ $p->name }}
                            </h6>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <span class="badge bg-light text-dark border font-size-11">
                                    <i class="bx bx-folder font-size-11 text-muted me-1"></i>{{ $p->category->name ?? 'Uncategorized' }}
                                </span>
                                @if($p->brand)
                                    <span class="text-muted font-size-11 ms-1"><i class="bx bx-tag font-size-11"></i> {{ $p->brand }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Key Metrics Row: Current Stock & Retail Price -->
                    <div class="inventory-card-metrics">
                        <div>
                            <span class="text-muted font-size-11 d-block text-uppercase fw-semibold">Current Stock</span>
                            <span class="font-monospace fw-bold font-size-14 text-dark">
                                {{ number_format($p->current_stock, 2) }}
                                <small class="text-muted fw-normal font-size-11">({{ $p->unit->name ?? 'Unit' }})</small>
                            </span>
                        </div>
                        <div class="text-end">
                            <span class="text-muted font-size-11 d-block text-uppercase fw-semibold">Retail Price</span>
                            <span class="font-monospace fw-bold font-size-14 text-success">
                                {{ currency() }} {{ number_format($p->sales_price, 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Expandable Details Row Toggle Button -->
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <button type="button" 
                                class="btn btn-sm btn-link text-decoration-none p-0 text-primary font-size-12 fw-semibold d-inline-flex align-items-center gap-1"
                                @click="expanded = !expanded">
                            <span x-text="expanded ? 'Hide Details' : 'View Details & Costs'"></span>
                            <i class="bx font-size-14 transition-icon" :class="expanded ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                        </button>
                        @if($p->barcode)
                            <small class="text-muted font-size-11"><i class="bx bx-barcode font-size-11"></i> {{ $p->barcode }}</small>
                        @endif
                    </div>

                    <!-- Expanded Secondary Columns / Data -->
                    <div x-show="expanded" x-collapse x-cloak class="inventory-detail-box">
                        <div class="inventory-detail-item">
                            <span class="text-muted">Purchase Cost:</span>
                            <span class="font-monospace text-dark fw-medium">{{ currency() }} {{ number_format($p->purchase_price, 2) }}</span>
                        </div>
                        <div class="inventory-detail-item">
                            <span class="text-muted">Average Cost:</span>
                            <span class="font-monospace text-muted">{{ currency() }} {{ number_format($p->weighted_cost, 2) }}</span>
                        </div>
                        <div class="inventory-detail-item">
                            <span class="text-muted">Unit of Measure:</span>
                            <span class="fw-medium text-dark">{{ $p->unit->name ?? 'Default Unit' }}</span>
                        </div>
                        <div class="inventory-detail-item">
                            <span class="text-muted">Min Stock Alert:</span>
                            <span class="text-dark">{{ number_format($p->min_stock, 2) }}</span>
                        </div>
                        @if($p->barcode)
                            <div class="inventory-detail-item">
                                <span class="text-muted">Barcode:</span>
                                <code>{{ $p->barcode }}</code>
                            </div>
                        @endif
                        @if($p->warehouse)
                            <div class="inventory-detail-item">
                                <span class="text-muted">Warehouse:</span>
                                <span class="text-dark">{{ $p->warehouse }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Touch-Friendly Action Buttons -->
                    <div class="inventory-card-actions d-flex gap-2 mt-3 pt-2 border-top">
                        <button type="button" wire:click="showProductDetails({{ $p->id }})" class="btn btn-sm btn-outline-info flex-grow-1" title="View Full Details">
                            <i class="bx bx-show me-1"></i> View
                        </button>
                        <button type="button" wire:click="editProduct({{ $p->id }})" class="btn btn-sm btn-outline-primary flex-grow-1" title="Edit Product">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </button>
                        <button type="button" onclick="confirm('Are you sure you want to delete this product?') || event.stopImmediatePropagation()" wire:click="deleteProduct({{ $p->id }})" class="btn btn-sm btn-outline-danger flex-grow-1" title="Delete Product">
                            <i class="bx bx-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-4">
                    <x-empty-state 
                        icon="bx bx-package" 
                        title="No products registered yet" 
                        message="Register your first product item to begin tracking stock, sales, and purchases."
                        :search="$search || $category_id_filter || $stock_status_filter"
                        addAction="openModal"
                        addLabel="Register Product" />
                </div>
            @endforelse
        </div>
    </x-table-card>

    <!-- Modals (Lazy Loaded) -->
    @if($viewProduct)
        @include('livewire.products.partials.product-view-modal')
    @endif

    @if($isModalOpen)
        @include('livewire.products.partials.product-modal')
    @endif
</div>
