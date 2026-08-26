<div>
    <!-- Page Header -->
    <x-page-header title="Customers Directory" subtitle="Manage customer accounts, credit limits, receivables, and contact details.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
            <i class="bx bx-plus me-1"></i> Register Customer
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-4 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Customers</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by name, company, code, phone, email...">
            </div>
        </div>
        <div class="col-lg-3 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Balance Status</label>
            <x-searchable-select wire:model.live="balance_filter" class="form-select" placeholder="All Balances">
                <option value="">All Balances</option>
                <option value="has_balance">Outstanding Receivable (&gt; 0)</option>
                <option value="zero_balance">Settled (0.00)</option>
            </x-searchable-select>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Status</label>
            <x-searchable-select wire:model.live="status_filter" class="form-select" placeholder="All Status">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </x-searchable-select>
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
        <div class="col-lg-2 col-md-4">
            <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                <i class="bx bx-reset me-1"></i> Reset Filters
            </button>
        </div>
    </x-filter-card>

    <!-- Customers Table -->
    <x-table-card target="search, status_filter, balance_filter, perPage, sortBy, resetFilters" loadingText="Loading customers..." :paginator="$customers">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="customer_code" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Code</x-th-sort>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection">Customer / Contact</x-th-sort>
                    <x-th-sort field="company_name" :sortField="$sortField" :sortDirection="$sortDirection">Company</x-th-sort>
                    <th>Contact Information</th>
                    <th>Credit Limit</th>
                    <x-th-sort field="current_balance" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Receivable Due</x-th-sort>
                    <th>Status</th>
                    <th class="text-center" style="width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td><code>{{ $c->customer_code }}</code></td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $c->name }}</span>
                            @if($c->contact_person)
                                <small class="text-muted d-block"><i class="bx bx-user font-size-11"></i> {{ $c->contact_person }}</small>
                            @endif
                        </td>
                        <td>{{ $c->company_name ?? '-' }}</td>
                        <td>
                            @if($c->mobile) <div><i class="bx bx-phone font-size-12 text-muted me-1"></i> {{ $c->mobile }}</div> @endif
                            @if($c->email) <div class="text-muted"><i class="bx bx-envelope font-size-12 text-muted me-1"></i> {{ $c->email }}</div> @endif
                        </td>
                        <td>AED {{ number_format($c->credit_limit, 2) }}</td>
                        <td class="text-end">
                            <span class="fw-bold {{ $c->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                AED {{ number_format($c->current_balance, 2) }}
                            </span>
                        </td>
                        <td>
                            <x-badge :type="$c->status ? 'success' : 'secondary'">
                                {{ $c->status ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </td>
                        <td class="text-center">
                            <button wire:click="viewCustomer({{ $c->id }})" class="btn btn-sm btn-outline-info me-1" title="View Customer Details">
                                <i class="bx bx-show"></i>
                            </button>
                            <button wire:click="editCustomer({{ $c->id }})" class="btn btn-sm btn-outline-primary" title="Edit Customer">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <button onclick="confirm('Are you sure you want to delete this customer?') || event.stopImmediatePropagation()" wire:click="deleteCustomer({{ $c->id }})" class="btn btn-sm btn-outline-danger ms-1" title="Delete Customer">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-empty-state 
                                icon="bx bx-users" 
                                title="No customers registered yet" 
                                message="Register your clients and customers to begin issuing sales invoices and tracking receivables."
                                :search="$search || $status_filter !== '' || $balance_filter"
                                addAction="openModal"
                                addLabel="Register Customer" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Customer Registration / Edit Modal -->
    @include('livewire.customers.partials.customer-modal')

    <!-- Customer Show Details Modal -->
    @include('livewire.customers.partials.customer-show-modal')
</div>
