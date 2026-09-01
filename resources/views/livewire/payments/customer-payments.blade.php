<div>
    <!-- Page Header -->
    <x-page-header title="Customer Receipts & Payments" subtitle="Record money received from customers, bank/cash deposits, and reconcile unpaid sales invoices.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-plus me-1"></i> Record Customer Receipt
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Search Receipts</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search receipt #, customer, ref...">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Filter Customer</label>
            <x-searchable-select wire:model.live="customer_id_filter" class="form-select" placeholder="All Customers">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Receiving Account</label>
            <x-searchable-select wire:model.live="account_id_filter" class="form-select" placeholder="All Accounts">
                <option value="">All Accounts</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
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

    <!-- Payments Table Card -->
    <x-table-card target="search, customer_id_filter, account_id_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading receipt vouchers..." :paginator="$payments">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="payment_number" :sortField="$sortField" :sortDirection="$sortDirection" width="130px">Receipt #</x-th-sort>
                    <x-th-sort field="payment_date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th style="min-width: 140px;">Customer</th>
                    <th style="min-width: 130px;">Deposited Account</th>
                    <x-th-sort field="amount" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Amount Received</x-th-sort>
                    <th>Reference / Cheque #</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td><code>{{ $p->payment_number }}</code></td>
                        <td>{{ $p->payment_date }}</td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $p->customer->name ?? 'Walk-in' }}</span>
                            @if($p->customer && $p->customer->company_name)
                                <small class="text-muted d-block">{{ $p->customer->company_name }}</small>
                            @endif
                        </td>
                        <td>
                            <x-badge type="info">{{ $p->account->name ?? 'Default Cash' }}</x-badge>
                        </td>
                        <td class="text-end fw-bold text-success font-size-14">
                            AED {{ number_format($p->amount, 2) }}
                        </td>
                        <td>{{ $p->reference_number ?? '-' }}</td>
                        <td><span class="text-muted font-size-12">{{ $p->notes ?? '-' }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state 
                                icon="bx bx-receipt" 
                                title="No customer payments recorded" 
                                message="Record customer receipts to reconcile outstanding balances and credit cash/bank accounts."
                                :search="$search || $customer_id_filter || $account_id_filter || $date_from || $date_to"
                                addAction="openModal"
                                addLabel="Record Customer Receipt" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Customer Payment Modal Form (Lazy Loaded) -->
    @if($isModalOpen)
        @include('livewire.payments.partials.customer-payment-modal')
    @endif
</div>
