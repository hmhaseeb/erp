<div>
    <!-- Page Header -->
    <x-page-header title="Stock Movements & Audit Log" subtitle="Track all inventory movements, purchases, sales deductions, and adjustments.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-slider-alt me-1"></i> Record Stock Adjustment
        </button>
    </x-page-header>

    <!-- Search & Filters Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Search Movements</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search product, SKU, note...">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Movement Type</label>
            <x-searchable-select wire:model.live="movement_type_filter" class="form-select" placeholder="All Types">
                <option value="">All Types</option>
                <option value="OPENING">OPENING</option>
                <option value="PURCHASE">PURCHASE</option>
                <option value="SALE">SALE</option>
                <option value="PURCHASE_RETURN">PURCHASE RETURN</option>
                <option value="SALES_RETURN">SALES RETURN</option>
                <option value="ADJUSTMENT_IN">ADJUSTMENT IN (+)</option>
                <option value="ADJUSTMENT_OUT">ADJUSTMENT OUT (-)</option>
            </x-searchable-select>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Filter Product</label>
            <x-searchable-select wire:model.live="product_id_filter" class="form-select" placeholder="All Products">
                <option value="">All Products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">From Date</label>
            <input type="date" wire:model.live="date_from" class="form-control">
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">To Date</label>
            <input type="date" wire:model.live="date_to" class="form-control">
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-1">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </x-searchable-select>
        </div>
        <x-slot:extra>
            <div class="col-12 text-sm-end text-center mt-1">
                <button type="button" wire:click="resetFilters" class="btn btn-sm btn-light">
                    <i class="bx bx-reset me-1"></i> Reset Filters
                </button>
            </div>
        </x-slot:extra>
    </x-filter-card>

    <!-- Stock Movements Table -->
    <x-table-card target="search, movement_type_filter, product_id_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading movements log..." :paginator="$movements">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th style="min-width: 140px;">Product</th>
                    <th>Type</th>
                    <th class="text-end text-success">Qty In (+)</th>
                    <th class="text-end text-danger">Qty Out (-)</th>
                    <th class="text-end">Unit Cost</th>
                    <th class="text-end">Total Valuation</th>
                    <th style="min-width: 140px;">Reference / Reason</th>
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
                                $badgeType = match($m->movement_type) {
                                    'OPENING' => 'secondary',
                                    'PURCHASE' => 'primary',
                                    'SALE' => 'info',
                                    'PURCHASE_RETURN' => 'warning',
                                    'SALES_RETURN' => 'success',
                                    'ADJUSTMENT_IN' => 'success',
                                    'ADJUSTMENT_OUT' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <x-badge :type="$badgeType">{{ $m->movement_type }}</x-badge>
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
                            <x-empty-state 
                                icon="bx bx-transfer" 
                                title="No stock movements logged yet" 
                                message="Stock movements will be recorded automatically during purchases, sales, and manual adjustments."
                                :search="$search || $movement_type_filter || $product_id_filter || $date_from || $date_to"
                                addAction="openModal"
                                addLabel="Record Adjustment" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Stock Adjustment Modal (Lazy Loaded) -->
    @if($isModalOpen)
        @include('livewire.products.partials.adjustment-modal')
    @endif
</div>
