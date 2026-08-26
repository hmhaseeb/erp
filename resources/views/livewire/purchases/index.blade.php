<div>
    <!-- Page Header -->
    <x-page-header title="Purchase Invoices" subtitle="Record vendor bills, goods received, payment terms, and inventory updates.">
        <a href="{{ route('purchases.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="bx bx-plus me-1"></i> New Purchase Invoice
        </a>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-3 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Purchases</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by Purchase #, supplier, ref...">
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Filter Supplier</label>
            <x-searchable-select wire:model.live="supplier_id_filter" class="form-select" placeholder="All Suppliers">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Payment Method</label>
            <x-searchable-select wire:model.live="payment_type_filter" class="form-select" placeholder="All Methods">
                <option value="">All Methods</option>
                <option value="Cash">Cash</option>
                <option value="Bank">Bank Transfer</option>
                <option value="Credit">Credit (Payable)</option>
            </x-searchable-select>
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
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </x-searchable-select>
        </div>
        <x-slot:extra>
            <div class="col-12 text-end">
                <button type="button" wire:click="resetFilters" class="btn btn-sm btn-light">
                    <i class="bx bx-reset me-1"></i> Reset Filters
                </button>
            </div>
        </x-slot:extra>
    </x-filter-card>

    <!-- Purchases Data Table Card -->
    <x-table-card target="search, supplier_id_filter, payment_type_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading purchase invoices..." :paginator="$purchases">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="purchase_number" :sortField="$sortField" :sortDirection="$sortDirection" width="130px">Purchase #</x-th-sort>
                    <x-th-sort field="purchase_date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th>Supplier</th>
                    <th>Payment Type</th>
                    <x-th-sort field="grand_total" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Grand Total</x-th-sort>
                    <th class="text-end">Paid Amount</th>
                    <th class="text-end">Due Amount</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $pur)
                    <tr>
                        <td><code>{{ $pur->purchase_number }}</code></td>
                        <td>{{ $pur->purchase_date }}</td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $pur->supplier->name ?? 'Unknown Supplier' }}</span>
                            @if($pur->supplier && $pur->supplier->company_name)
                                <small class="text-muted d-block">{{ $pur->supplier->company_name }}</small>
                            @endif
                        </td>
                        <td>
                            <x-badge :type="$pur->payment_type === 'Cash' ? 'success' : ($pur->payment_type === 'Bank' ? 'info' : 'warning')">
                                {{ $pur->payment_type }}
                            </x-badge>
                        </td>
                        <td class="text-end fw-bold text-dark">AED {{ number_format($pur->grand_total, 2) }}</td>
                        <td class="text-end text-success">AED {{ number_format($pur->paid_amount, 2) }}</td>
                        <td class="text-end font-monospace {{ $pur->due_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                            AED {{ number_format($pur->due_amount, 2) }}
                        </td>
                        <td>
                            <x-badge :type="$pur->status === 'Confirmed' ? 'success' : ($pur->status === 'Cancelled' ? 'danger' : 'warning')">
                                {{ $pur->status }}
                            </x-badge>
                        </td>
                        <td class="text-center">
                            <button wire:click="viewDetails({{ $pur->id }})" class="btn btn-sm btn-outline-primary" title="View Purchase Details">
                                <i class="bx bx-show me-1"></i> View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <x-empty-state 
                                icon="bx bx-shopping-bag" 
                                title="No purchase invoices created yet" 
                                message="Create your first purchase invoice to receive inventory stock and record payables."
                                :search="$search || $supplier_id_filter || $payment_type_filter || $date_from || $date_to">
                                <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-plus me-1"></i> New Purchase Invoice
                                </a>
                            </x-empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Purchase Details Modal -->
    @include('livewire.purchases.partials.purchase-details-modal')
</div>
