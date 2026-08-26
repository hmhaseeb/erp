<div>
    <!-- Page Header -->
    <x-page-header title="Account Transaction Entries" subtitle="Audit and manage debit and credit ledger postings, cash entries, and fund transfers.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
            <i class="bx bx-transfer me-1"></i> New Transaction / Transfer
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-3 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Transactions</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search description, type, amount...">
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Filter Account</label>
            <x-searchable-select wire:model.live="account_id_filter" class="form-select" placeholder="All Accounts">
                <option value="">All Accounts</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Transaction Type</label>
            <x-searchable-select wire:model.live="type_filter" class="form-select" placeholder="All Types">
                <option value="">All Types</option>
                <option value="Cash In">Cash In</option>
                <option value="Cash Out">Cash Out</option>
                <option value="Bank Deposit">Bank Deposit</option>
                <option value="Bank Withdrawal">Bank Withdrawal</option>
                <option value="Transfer">Transfer</option>
                <option value="Sale">Sale Receipt</option>
                <option value="Purchase">Purchase Payment</option>
                <option value="Income">Income Entry</option>
                <option value="Expense">Expense Entry</option>
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
                <option value="15">15</option>
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

    <!-- Transactions Data Table -->
    <x-table-card target="search, account_id_filter, type_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading transaction entries..." :paginator="$transactions">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="transaction_date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th>Account</th>
                    <th>Type</th>
                    <th>Description</th>
                    <x-th-sort field="debit" :sortField="$sortField" :sortDirection="$sortDirection" align="right" class="text-success">Debit (Inflow +)</x-th-sort>
                    <x-th-sort field="credit" :sortField="$sortField" :sortDirection="$sortDirection" align="right" class="text-danger">Credit (Outflow -)</x-th-sort>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                    <tr>
                        <td>{{ $t->transaction_date }}</td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $t->account->name ?? 'Account' }}</span>
                        </td>
                        <td>
                            <x-badge :type="in_array($t->transaction_type, ['Cash In', 'Bank Deposit', 'Income', 'Sale', 'Customer Payment']) ? 'success' : 'danger'">
                                {{ $t->transaction_type }}
                            </x-badge>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 320px;" title="{{ $t->description }}">
                                {{ $t->description ?? '-' }}
                            </span>
                        </td>
                        <td class="text-end font-monospace {{ $t->debit > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                            {{ $t->debit > 0 ? 'AED ' . number_format($t->debit, 2) : '-' }}
                        </td>
                        <td class="text-end font-monospace {{ $t->credit > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                            {{ $t->credit > 0 ? 'AED ' . number_format($t->credit, 2) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-empty-state 
                                icon="bx bx-transfer" 
                                title="No transactions found" 
                                message="Transactions will be posted automatically as sales, purchases, and income/expenses are recorded."
                                :search="$search || $account_id_filter || $type_filter || $date_from || $date_to"
                                addAction="openModal"
                                addLabel="New Transaction / Transfer" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Manual Transaction / Transfer Modal -->
    @include('livewire.accounts.partials.transaction-modal')
</div>
