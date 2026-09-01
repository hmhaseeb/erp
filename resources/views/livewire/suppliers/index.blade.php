<div>
    <!-- Page Header -->
    <x-page-header title="Suppliers Directory" subtitle="Manage vendor profiles, contact details, payables, and transactions.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-plus me-1"></i> Register Supplier
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-4">
            <label class="form-label font-size-12 text-muted mb-1">Search Suppliers</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by name, company, code, phone, email...">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Balance Status</label>
            <x-searchable-select wire:model.live="balance_filter" class="form-select" placeholder="All Balances">
                <option value="">All Balances</option>
                <option value="has_balance">Outstanding Payable (&gt; 0)</option>
                <option value="zero_balance">Settled (0.00)</option>
            </x-searchable-select>
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Status</label>
            <x-searchable-select wire:model.live="status_filter" class="form-select" placeholder="All Status">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </x-searchable-select>
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-1">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="10">10</option>
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

    <!-- Suppliers Table -->
    <x-table-card target="search, status_filter, balance_filter, perPage, sortBy, resetFilters" loadingText="Loading suppliers..." :paginator="$suppliers">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="supplier_code" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Code</x-th-sort>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection" style="min-width: 140px;">Supplier / Contact</x-th-sort>
                    <x-th-sort field="company_name" :sortField="$sortField" :sortDirection="$sortDirection" style="min-width: 130px;">Company</x-th-sort>
                    <th style="min-width: 140px;">Contact Information</th>
                    <th>TRN / VAT #</th>
                    <x-th-sort field="current_balance" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Payable Balance</x-th-sort>
                    <th>Status</th>
                    <th class="text-center text-nowrap" style="min-width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $s)
                    <tr>
                        <td><code>{{ $s->supplier_code }}</code></td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $s->name }}</span>
                            @if($s->contact_person)
                                <small class="text-muted d-block"><i class="bx bx-user font-size-11"></i> {{ $s->contact_person }}</small>
                            @endif
                        </td>
                        <td>{{ $s->company_name ?? '-' }}</td>
                        <td>
                            @if($s->mobile) <div><i class="bx bx-phone font-size-12 text-muted me-1"></i> {{ $s->mobile }}</div> @endif
                            @if($s->email) <div class="text-muted"><i class="bx bx-envelope font-size-12 text-muted me-1"></i> {{ $s->email }}</div> @endif
                        </td>
                        <td>{{ $s->trn_number ?? '-' }}</td>
                        <td class="text-end">
                            <span class="fw-bold {{ $s->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                AED {{ number_format($s->current_balance, 2) }}
                            </span>
                        </td>
                        <td>
                            <x-badge :type="$s->status ? 'success' : 'secondary'">
                                {{ $s->status ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </td>
                        <td class="text-center">
                            <button wire:click="viewSupplier({{ $s->id }})" class="btn btn-sm btn-outline-info me-1" title="View Supplier Details">
                                <i class="bx bx-show"></i>
                            </button>
                            <button wire:click="editSupplier({{ $s->id }})" class="btn btn-sm btn-outline-primary" title="Edit Supplier">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <button onclick="confirm('Are you sure you want to delete this supplier?') || event.stopImmediatePropagation()" wire:click="deleteSupplier({{ $s->id }})" class="btn btn-sm btn-outline-danger ms-1" title="Delete Supplier">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-empty-state 
                                icon="bx bx-truck" 
                                title="No suppliers registered yet" 
                                message="Register your vendors and suppliers to begin recording purchase invoices."
                                :search="$search || $status_filter !== '' || $balance_filter"
                                addAction="openModal"
                                addLabel="Register Supplier" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Supplier Registration / Edit Modal (Lazy Loaded) -->
    @if($isModalOpen)
        @include('livewire.suppliers.partials.supplier-modal')
    @endif

    <!-- Supplier Show Details Modal (Lazy Loaded) -->
    @if($isViewModalOpen)
        @include('livewire.suppliers.partials.supplier-show-modal')
    @endif
</div>
