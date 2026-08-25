<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Product Catalog & Inventory</h4>
                    <p class="text-muted font-size-13 mb-0">Manage product items, pictures, prices, barcodes, and stock levels.</p>
                </div>
                <div class="page-title-right">
                    <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-plus me-1"></i> Register New Product
                    </button>
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
                    <label class="form-label font-size-12 text-muted mb-1">Filter by Category</label>
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
                        <option value="">All Stock Status</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock Alert</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
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
                <div class="col-lg-2 col-md-4">
                    <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                        <i class="bx bx-reset me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <!-- Loading Overlay -->
            <div wire:loading.flex wire:target="search, category_id_filter, stock_status_filter, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading records...</span>
            </div>

            <div wire:loading.remove wire:target="search, category_id_filter, stock_status_filter, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('product_code')" class="sortable" style="width: 110px;">
                                SKU / Code
                                @if($sortField === 'product_code')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('name')" class="sortable">
                                Product Details
                                @if($sortField === 'name')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th wire:click="sortBy('purchase_price')" class="sortable text-end">
                                Purchase Cost
                                @if($sortField === 'purchase_price')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('sales_price')" class="sortable text-end">
                                Retail Price
                                @if($sortField === 'sales_price')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('weighted_cost')" class="sortable text-end">
                                Avg Cost
                                @if($sortField === 'weighted_cost')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('current_stock')" class="sortable text-end">
                                Current Stock
                                @if($sortField === 'current_stock')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Status</th>
                            <th class="text-center" style="width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                            <tr>
                                <td><code>{{ $p->product_code }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center" style="cursor: pointer;" wire:click="showProductDetails({{ $p->id }})" title="Click to view details">
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
                                <td><span class="badge badge-soft-secondary font-size-12">{{ $p->unit->name ?? '-' }}</span></td>
                                <td class="text-end text-muted font-monospace">AED {{ number_format($p->purchase_price, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">AED {{ number_format($p->sales_price, 2) }}</td>
                                <td class="text-end text-muted font-monospace">AED {{ number_format($p->weighted_cost, 2) }}</td>
                                <td class="text-end font-monospace">
                                    @if($p->current_stock <= 0)
                                        <span class="badge badge-soft-danger font-size-12">0.00 (Out of Stock)</span>
                                    @elseif($p->current_stock <= $p->min_stock)
                                        <span class="badge badge-soft-warning font-size-12">{{ number_format($p->current_stock, 2) }} (Low)</span>
                                    @else
                                        <span class="badge badge-soft-success font-size-12">{{ number_format($p->current_stock, 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $p->status ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                        {{ $p->status ? 'Active' : 'Inactive' }}
                                    </span>
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
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-package"></i>
                                        </div>
                                        @if($search || $category_id_filter || $stock_status_filter)
                                            <h6 class="text-dark">No matching products found</h6>
                                            <p class="text-muted font-size-13 mb-3">No products match your current search or filter criteria. Try adjusting your query.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Clear Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No products registered yet</h6>
                                            <p class="text-muted font-size-13 mb-3">Register your first product item to begin tracking stock, sales, and purchases.</p>
                                            <button wire:click="openModal" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> Register Product
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
                    Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} records
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Single Product View Modal -->
    @if($viewProduct)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.55);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary font-size-12">{{ $viewProduct->product_code }}</span>
                            <h5 class="modal-title mb-0 text-dark fw-bold">{{ $viewProduct->name }}</h5>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4 mb-4">
                            <!-- Left: Product Image -->
                            <div class="col-md-4 text-center">
                                <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-center" style="min-height: 200px; height: 100%;">
                                    @if($viewProduct->image)
                                        <img src="{{ asset('storage/' . $viewProduct->image) }}" alt="{{ $viewProduct->name }}" class="img-fluid rounded object-fit-contain" style="max-height: 200px; max-width: 100%;">
                                    @else
                                        <div class="text-muted py-4">
                                            <i class="bx bx-image font-size-48 d-block mb-1"></i>
                                            <span class="font-size-12">No Picture Uploaded</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Right: Basic Specs & Status -->
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge {{ $viewProduct->status ? 'badge-soft-success' : 'badge-soft-secondary' }} font-size-12">
                                            {{ $viewProduct->status ? 'Active in Catalog' : 'Inactive' }}
                                        </span>
                                        @if($viewProduct->current_stock <= 0)
                                            <span class="badge badge-soft-danger font-size-12 ms-1">Out of Stock</span>
                                        @elseif($viewProduct->current_stock <= $viewProduct->min_stock)
                                            <span class="badge badge-soft-warning font-size-12 ms-1">Low Stock Alert</span>
                                        @else
                                            <span class="badge badge-soft-success font-size-12 ms-1">In Stock</span>
                                        @endif
                                    </div>
                                    <span class="text-muted font-size-12">Registered: {{ $viewProduct->created_at ? $viewProduct->created_at->format('d M Y') : '-' }}</span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0 font-size-13">
                                        <tbody>
                                            <tr>
                                                <th class="text-muted ps-0" style="width: 130px;">Category:</th>
                                                <td class="fw-semibold text-dark">{{ $viewProduct->category->name ?? 'Uncategorized' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted ps-0">Brand / Maker:</th>
                                                <td class="fw-semibold text-dark">{{ $viewProduct->brand ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted ps-0">Barcode:</th>
                                                <td><code>{{ $viewProduct->barcode ?? '-' }}</code></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted ps-0">Unit of Measure:</th>
                                                <td><span class="badge badge-soft-secondary">{{ $viewProduct->unit->name ?? 'Default Unit' }}</span></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted ps-0">Warehouse:</th>
                                                <td class="text-dark">{{ $viewProduct->warehouse ?? 'Main Warehouse' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Financial & Stock Summary Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-6">
                                <div class="p-3 bg-light rounded border text-center">
                                    <span class="text-muted font-size-12 d-block">Cost Price</span>
                                    <h5 class="mb-0 text-dark fw-bold font-monospace">AED {{ number_format($viewProduct->purchase_price, 2) }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 bg-light rounded border text-center">
                                    <span class="text-muted font-size-12 d-block">Retail Price</span>
                                    <h5 class="mb-0 text-success fw-bold font-monospace">AED {{ number_format($viewProduct->sales_price, 2) }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 bg-light rounded border text-center">
                                    <span class="text-muted font-size-12 d-block">VAT Rate</span>
                                    <h5 class="mb-0 text-info fw-bold font-monospace">{{ number_format($viewProduct->tax_percent, 1) }}%</h5>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 bg-light rounded border text-center">
                                    <span class="text-muted font-size-12 d-block">Gross Margin</span>
                                    @php
                                        $margin = $viewProduct->sales_price > 0 ? (($viewProduct->sales_price - $viewProduct->purchase_price) / $viewProduct->sales_price) * 100 : 0;
                                    @endphp
                                    <h5 class="mb-0 text-primary fw-bold font-monospace">{{ number_format($margin, 1) }}%</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Level Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-muted font-size-12 d-block">Current Stock on Hand</span>
                                    <h4 class="mb-0 text-dark fw-bold font-monospace mt-1">
                                        {{ number_format($viewProduct->current_stock, 2) }} <small class="font-size-13 text-muted">{{ $viewProduct->unit->name ?? '' }}</small>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-muted font-size-12 d-block">Min Stock Threshold</span>
                                    <h4 class="mb-0 text-muted fw-bold font-monospace mt-1">
                                        {{ number_format($viewProduct->min_stock, 2) }} <small class="font-size-13 text-muted">{{ $viewProduct->unit->name ?? '' }}</small>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded border">
                                    <span class="text-muted font-size-12 d-block">Total Asset Valuation</span>
                                    <h4 class="mb-0 text-primary fw-bold font-monospace mt-1">
                                        AED {{ number_format($viewProduct->current_stock * $viewProduct->weighted_cost, 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Description Section -->
                        @if($viewProduct->description)
                            <div class="mb-4">
                                <label class="form-label text-muted font-size-12 fw-bold text-uppercase">Description / Specifications</label>
                                <div class="p-3 bg-light rounded border font-size-13 text-dark">
                                    {{ $viewProduct->description }}
                                </div>
                            </div>
                        @endif

                        <!-- Recent Stock Activity -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label text-muted font-size-12 fw-bold text-uppercase mb-0">Recent Stock Activity (Last 6 Transactions)</label>
                                <a href="{{ route('products.stock') }}?product_id={{ $viewProduct->id }}" class="btn btn-sm btn-link p-0 font-size-12">View Full Ledger</a>
                            </div>
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-hover mb-0 font-size-12">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Movement</th>
                                            <th class="text-end">Qty In</th>
                                            <th class="text-end">Qty Out</th>
                                            <th class="text-end">Unit Cost</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($viewProduct->stockMovements as $mov)
                                            <tr>
                                                <td>{{ $mov->date }}</td>
                                                <td>
                                                    <span class="badge badge-soft-info">{{ $mov->movement_type }}</span>
                                                </td>
                                                <td class="text-end text-success font-monospace">{{ $mov->quantity_in > 0 ? '+' . number_format($mov->quantity_in, 2) : '-' }}</td>
                                                <td class="text-end text-danger font-monospace">{{ $mov->quantity_out > 0 ? '-' . number_format($mov->quantity_out, 2) : '-' }}</td>
                                                <td class="text-end font-monospace text-muted">AED {{ number_format($mov->unit_cost, 2) }}</td>
                                                <td class="text-muted">{{ $mov->notes ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-3 text-muted">No stock movements recorded for this product yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" wire:click="editProduct({{ $viewProduct->id }})" class="btn btn-primary">
                            <i class="bx bx-edit me-1"></i> Edit Product
                        </button>
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- 2. Product Register/Edit Modal Form -->
    @if($isModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.55);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditMode ? 'Edit Product Item' : 'Register New Product' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveProduct">
                        <div class="modal-body">
                            <!-- 1. Basic Information -->
                            <div class="form-section-title">1. Basic Information</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Product Code / SKU <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="product_code" class="form-control @error('product_code') is-invalid @enderror" placeholder="e.g. PROD-00001">
                                    @error('product_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Barcode (Optional)</label>
                                    <input type="text" wire:model="barcode" class="form-control @error('barcode') is-invalid @enderror" placeholder="Scan or enter barcode...">
                                    @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter product title or description">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Brand / Manufacturer</label>
                                    <input type="text" wire:model="brand" class="form-control" placeholder="e.g. Apple, Dell, HP">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category</label>
                                    <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Unit of Measure</label>
                                    <select wire:model="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                        <option value="">Select Unit</option>
                                        @foreach($units as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- 2. Pricing & VAT -->
                            <div class="form-section-title mt-2">2. Pricing & VAT Details</div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Purchase / Cost Price (AED) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror">
                                    @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Selling Price (AED) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model="sales_price" class="form-control @error('sales_price') is-invalid @enderror">
                                    @error('sales_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">VAT Tax %</label>
                                    <input type="number" step="0.01" wire:model="tax_percent" class="form-control @error('tax_percent') is-invalid @enderror">
                                    @error('tax_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- 3. Inventory & Warehouse -->
                            <div class="form-section-title mt-2">3. Inventory & Warehouse</div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Min Stock Alert Level</label>
                                    <input type="number" step="1" wire:model="min_stock" class="form-control @error('min_stock') is-invalid @enderror">
                                    @error('min_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                @if(!$isEditMode)
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Opening Stock Quantity</label>
                                        <input type="number" step="1" wire:model="opening_stock" class="form-control @error('opening_stock') is-invalid @enderror">
                                        @error('opening_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                @endif
                                <div class="{{ $isEditMode ? 'col-md-8' : 'col-md-4' }} mb-3">
                                    <label class="form-label">Warehouse Location</label>
                                    <input type="text" wire:model="warehouse" class="form-control">
                                </div>
                            </div>

                            <!-- 4. Product Image & Presentation -->
                            <div class="form-section-title mt-2">4. Product Image & Presentation</div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Product Image</label>
                                    <div class="p-3 bg-light rounded border d-flex align-items-center gap-3">
                                        <div>
                                            @if ($image)
                                                <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="rounded border shadow-sm object-fit-cover" style="width: 76px; height: 76px;">
                                            @elseif ($existingImage)
                                                <img src="{{ asset('storage/' . $existingImage) }}" alt="Existing Product" class="rounded border shadow-sm object-fit-cover" style="width: 76px; height: 76px;">
                                            @else
                                                <div class="rounded border bg-white text-muted d-flex align-items-center justify-content-center shadow-sm" style="width: 76px; height: 76px;">
                                                    <i class="bx bx-image font-size-32"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" wire:model="image" accept="image/*" class="form-control form-control-sm @error('image') is-invalid @enderror">
                                            <div wire:loading wire:target="image" class="text-primary font-size-12 mt-1">
                                                <i class="bx bx-loader-alt bx-spin me-1"></i> Uploading & previewing image...
                                            </div>
                                            @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-muted font-size-11">Formats: JPG, PNG, WEBP, SVG (Max: 2MB)</small>
                                                @if($image || $existingImage)
                                                    <button type="button" wire:click="removeImage" class="btn btn-sm btn-link text-danger p-0 font-size-12">
                                                        <i class="bx bx-trash me-1"></i> Remove Image
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Description / Notes</label>
                                <textarea wire:model="description" class="form-control" rows="2" placeholder="Optional product specifications, model numbers, or notes..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="saveProduct">{{ $isEditMode ? 'Update Product' : 'Save Product' }}</span>
                                <span wire:loading wire:target="saveProduct"><i class="bx bx-loader-alt bx-spin me-1"></i> Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
