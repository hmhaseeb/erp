<div>
    <!-- Page Header -->
    <x-page-header title="Income Transactions" subtitle="Record non-sales revenues, services, commissions, and other financial inflows.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-plus me-1"></i> Record Income
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Search Income</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search description, category, ref...">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Category</label>
            <x-searchable-select wire:model.live="category_id_filter" class="form-select" placeholder="All Categories">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Deposit Account</label>
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
                <option value="12">12</option>
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

    <!-- Income Table Card -->
    <x-table-card target="search, category_id_filter, account_id_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading income entries..." :paginator="$incomes">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th style="min-width: 130px;">Category</th>
                    <th style="min-width: 140px;">Description</th>
                    <th style="min-width: 130px;">Account</th>
                    <x-th-sort field="amount" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Amount</x-th-sort>
                    <th>Reference #</th>
                    <th>Receipt / Attachment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incomes as $inc)
                    <tr>
                        <td>{{ $inc->date }}</td>
                        <td>
                            <x-badge type="success">{{ $inc->category->name ?? 'Income' }}</x-badge>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $inc->description ?? '-' }}</span>
                            @if($inc->notes) <small class="text-muted d-block">{{ $inc->notes }}</small> @endif
                        </td>
                        <td>{{ $inc->account->name ?? 'Default Cash' }}</td>
                        <td class="text-end fw-bold text-success font-size-14 font-monospace">
                            AED {{ number_format($inc->amount, 2) }}
                        </td>
                        <td>{{ $inc->reference_number ?? '-' }}</td>
                        <td>
                            @if($inc->attachment)
                                <a href="{{ asset('storage/' . $inc->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bx bx-paperclip me-1"></i> View Attachment
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
                                icon="bx bx-trending-up" 
                                title="No income entries recorded yet" 
                                message="Record services, commission, rent, or other income streams."
                                :search="$search || $category_id_filter || $account_id_filter || $date_from || $date_to"
                                addAction="openModal"
                                addLabel="Record Income" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Income Modal Form (Lazy Loaded) -->
    @if($isModalOpen)
        @include('livewire.income.partials.income-modal')
    @endif
</div>
