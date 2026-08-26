<div>
    <!-- Page Header -->
    <x-page-header title="Supplier Payment Vouchers" subtitle="Record payments made to suppliers, bank/cash disbursements, and reconcile unpaid purchase bills.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
            <i class="bx bx-plus me-1"></i> Record Supplier Payment
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-3 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Payments</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search voucher #, supplier, ref...">
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
            <label class="form-label font-size-12 text-muted mb-1">Paid From Account</label>
            <x-searchable-select wire:model.live="account_id_filter" class="form-select" placeholder="All Accounts">
                <option value="">All Accounts</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                @endforeach
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

    <!-- Supplier Payments Table Card -->
    <x-table-card target="search, supplier_id_filter, account_id_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading payment vouchers..." :paginator="$payments">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="payment_number" :sortField="$sortField" :sortDirection="$sortDirection" width="130px">Voucher #</x-th-sort>
                    <x-th-sort field="payment_date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th>Supplier</th>
                    <th>Disbursed From Account</th>
                    <x-th-sort field="amount" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Amount Paid</x-th-sort>
                    <th>Reference #</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td><code>{{ $p->payment_number }}</code></td>
                        <td>{{ $p->payment_date }}</td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $p->supplier->name ?? 'Supplier' }}</span>
                            @if($p->supplier && $p->supplier->company_name)
                                <small class="text-muted d-block">{{ $p->supplier->company_name }}</small>
                            @endif
                        </td>
                        <td>
                            <x-badge type="primary">{{ $p->account->name ?? 'Default Cash' }}</x-badge>
                        </td>
                        <td class="text-end fw-bold text-danger font-size-14">
                            AED {{ number_format($p->amount, 2) }}
                        </td>
                        <td>{{ $p->reference_number ?? '-' }}</td>
                        <td><span class="text-muted font-size-12">{{ $p->notes ?? '-' }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state 
                                icon="bx bx-dollar-circle" 
                                title="No supplier payment vouchers found" 
                                message="Record supplier disbursements to settle vendor bills and reduce account balances."
                                :search="$search || $supplier_id_filter || $account_id_filter || $date_from || $date_to"
                                addAction="openModal"
                                addLabel="Record Supplier Payment" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Supplier Payment Modal Form -->
    @include('livewire.payments.partials.supplier-payment-modal')
</div>
