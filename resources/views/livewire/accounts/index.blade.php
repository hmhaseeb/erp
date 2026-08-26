<div>
    <!-- Page Header -->
    <x-page-header title="Cash & Bank Accounts" subtitle="Manage company liquid assets, cash drawers, bank accounts, and current balances.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
            <i class="bx bx-plus me-1"></i> Add New Account
        </button>
    </x-page-header>

    <!-- Summary KPI Cards -->
    <div class="row mb-3">
        <x-kpi-card col="col-md-4" title="Cash Drawers Balance" :value="number_format($totalCash, 2)" prefix="AED " color="success" icon="bx bx-money" />
        <x-kpi-card col="col-md-4" title="Bank Accounts Balance" :value="number_format($totalBank, 2)" prefix="AED " color="primary" icon="bx bx-buildings" />
        <x-kpi-card col="col-md-4" title="Total Liquid Funds" :value="number_format($totalLiquid, 2)" prefix="AED " color="dark" icon="bx bx-wallet" />
    </div>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-6 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Accounts</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search account name, bank name, account number...">
            </div>
        </div>
        <div class="col-lg-3 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Account Type</label>
            <x-searchable-select wire:model.live="type_filter" class="form-select" placeholder="All Types">
                <option value="">All Types</option>
                <option value="Cash">Cash Account</option>
                <option value="Bank">Bank Account</option>
                <option value="Other">Other</option>
            </x-searchable-select>
        </div>
        <div class="col-lg-1 col-md-2">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </x-searchable-select>
        </div>
        <div class="col-lg-2 col-md-4">
            <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                <i class="bx bx-reset me-1"></i> Reset
            </button>
        </div>
    </x-filter-card>

    <!-- Accounts Table Card -->
    <x-table-card target="search, type_filter, perPage, sortBy, resetFilters" loadingText="Loading accounts..." :paginator="$accounts">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection">Account Title</x-th-sort>
                    <th>Type</th>
                    <th>Bank Name</th>
                    <th>Account # / IBAN</th>
                    <th class="text-end">Opening Balance</th>
                    <x-th-sort field="current_balance" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Current Balance</x-th-sort>
                    <th>Status</th>
                    <th class="text-center" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $acc)
                    <tr>
                        <td>
                            <span class="fw-semibold text-dark">{{ $acc->name }}</span>
                            @if($acc->notes) <small class="text-muted d-block">{{ $acc->notes }}</small> @endif
                        </td>
                        <td>
                            <x-badge :type="$acc->type === 'Cash' ? 'success' : 'primary'">{{ $acc->type }}</x-badge>
                        </td>
                        <td>{{ $acc->bank_name ?? '-' }}</td>
                        <td><code>{{ $acc->account_number ?? '-' }}</code></td>
                        <td class="text-end text-muted">AED {{ number_format($acc->opening_balance, 2) }}</td>
                        <td class="text-end fw-bold font-size-14 {{ $acc->current_balance >= 0 ? 'text-success' : 'text-danger' }}">
                            AED {{ number_format($acc->current_balance, 2) }}
                        </td>
                        <td>
                            <x-badge :type="$acc->status ? 'success' : 'secondary'">{{ $acc->status ? 'Active' : 'Inactive' }}</x-badge>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('accounts.ledger') }}?account_id={{ $acc->id }}" class="btn btn-sm btn-outline-info" title="View Account Ledger">
                                <i class="bx bx-book-open"></i>
                            </a>
                            <button wire:click="editAccount({{ $acc->id }})" class="btn btn-sm btn-outline-primary ms-1" title="Edit Account">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <button onclick="confirm('Are you sure you want to delete this account?') || event.stopImmediatePropagation()" wire:click="deleteAccount({{ $acc->id }})" class="btn btn-sm btn-outline-danger ms-1" title="Delete Account">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-empty-state 
                                icon="bx bx-wallet" 
                                title="No accounts found" 
                                message="Add cash drawers or bank accounts to start recording collections, disbursements, and expenses."
                                :search="$search || $type_filter"
                                addAction="openModal"
                                addLabel="Add Account" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Account Modal -->
    @include('livewire.accounts.partials.account-modal')
</div>
