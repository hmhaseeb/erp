<div>
    <!-- Page Header -->
    <x-page-header title="Operating Expenses" subtitle="Record and track overhead costs, utilities, rent, salaries, and office disbursements.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
            <i class="bx bx-plus me-1"></i> Record Expense
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-3 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Expenses</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search description, category, ref...">
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Category</label>
            <x-searchable-select wire:model.live="category_id_filter" class="form-select" placeholder="All Categories">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
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
                <option value="12">12</option>
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

    <!-- Expenses Table Card -->
    <x-table-card target="search, category_id_filter, account_id_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading expenses..." :paginator="$expenses">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Paid Account</th>
                    <x-th-sort field="amount" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Amount</x-th-sort>
                    <th>Reference #</th>
                    <th>Receipt / Attachment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                    <tr>
                        <td>{{ $exp->date }}</td>
                        <td>
                            <x-badge type="danger">{{ $exp->category->name ?? 'Expense' }}</x-badge>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $exp->description ?? '-' }}</span>
                            @if($exp->notes) <small class="text-muted d-block">{{ $exp->notes }}</small> @endif
                        </td>
                        <td>{{ $exp->account->name ?? 'Default Cash' }}</td>
                        <td class="text-end fw-bold text-danger font-size-14 font-monospace">
                            AED {{ number_format($exp->amount, 2) }}
                        </td>
                        <td>{{ $exp->reference_number ?? '-' }}</td>
                        <td>
                            @if($exp->attachment)
                                <a href="{{ asset('storage/' . $exp->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bx bx-paperclip me-1"></i> View Receipt
                                </a>
                            @else
                                <span class="text-muted font-size-12">None</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state 
                                icon="bx bx-trending-down" 
                                title="No expense entries recorded yet" 
                                message="Record operating costs such as rent, utility bills, office supplies, and salaries."
                                :search="$search || $category_id_filter || $account_id_filter || $date_from || $date_to"
                                addAction="openModal"
                                addLabel="Record Expense" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Expense Modal Form -->
    @include('livewire.expenses.partials.expense-modal')
</div>
