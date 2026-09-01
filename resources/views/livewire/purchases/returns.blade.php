<div>
    <!-- Page Header -->
    <x-page-header title="Purchase Returns / Debit Notes" subtitle="Record goods returned to suppliers, inventory deduction, and payable credit adjustments.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-plus me-1"></i> New Purchase Return
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Search Returns</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by Return #, supplier, reason...">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Filter Supplier</label>
            <x-searchable-select wire:model.live="supplier_id_filter" class="form-select" placeholder="All Suppliers">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
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
        <div class="col-6 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
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

    <!-- Purchase Returns Table -->
    <x-table-card target="search, supplier_id_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading returns..." :paginator="$returns">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="return_number" :sortField="$sortField" :sortDirection="$sortDirection" width="130px">Return #</x-th-sort>
                    <x-th-sort field="return_date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th style="min-width: 140px;">Supplier</th>
                    <th style="min-width: 150px;">Items Returned</th>
                    <x-th-sort field="grand_total" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Return Total</x-th-sort>
                    <th style="min-width: 140px;">Reason / Note</th>
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
                            <x-badge type="success">{{ $r->status ?? 'Confirmed' }}</x-badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state 
                                icon="bx bx-undo" 
                                title="No purchase returns processed" 
                                message="Record purchase returns to debit supplier accounts and deduct returned items from stock."
                                :search="$search || $supplier_id_filter || $date_from || $date_to"
                                addAction="openModal"
                                addLabel="New Purchase Return" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Purchase Return Modal Form (Lazy Loaded) -->
    @if($isModalOpen)
        @include('livewire.purchases.partials.return-modal')
    @endif
</div>
