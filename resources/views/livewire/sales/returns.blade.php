<div>
    <!-- Page Header -->
    <x-page-header title="Sales Returns / Credit Notes" subtitle="Record items returned by customers, restore stock inventory, and issue receivable credits.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-plus me-1"></i> New Sales Return
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Search Returns</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by Return #, customer, reason...">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Filter Customer</label>
            <x-searchable-select wire:model.live="customer_id_filter" class="form-select" placeholder="All Customers">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
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

    <!-- Sales Returns Table -->
    <x-table-card target="search, customer_id_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading returns..." :paginator="$returns">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="return_number" :sortField="$sortField" :sortDirection="$sortDirection" width="130px">Return #</x-th-sort>
                    <x-th-sort field="return_date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th style="min-width: 140px;">Customer</th>
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
                            <span class="fw-semibold text-dark">{{ $r->customer->name ?? 'Unknown Customer' }}</span>
                            @if($r->customer && $r->customer->company_name)
                                <small class="text-muted d-block">{{ $r->customer->company_name }}</small>
                            @endif
                        </td>
                        <td>
                            @foreach($r->items as $item)
                                <div>{{ $item->product->name ?? 'Product' }} ({{ number_format($item->quantity, 2) }})</div>
                            @endforeach
                        </td>
                        <td class="text-end fw-bold text-success">
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
                                title="No sales returns processed" 
                                message="Record customer returns to restore stock to inventory and credit customer account."
                                :search="$search || $customer_id_filter || $date_from || $date_to"
                                addAction="openModal"
                                addLabel="New Sales Return" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Sales Return Modal Form (Lazy Loaded) -->
    @if($isModalOpen)
        @include('livewire.sales.partials.return-modal')
    @endif
</div>
