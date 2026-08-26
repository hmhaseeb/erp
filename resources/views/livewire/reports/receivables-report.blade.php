<div>
    <!-- Page Header -->
    <x-page-header title="Accounts Receivable Aging & Report" subtitle="Track outstanding balances due from customers and drill down into unpaid sales invoices.">
        <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
            <i class="bx bx-printer me-1"></i> Print Receivables
        </button>
    </x-page-header>

    <!-- Summary KPI Cards -->
    <div class="row mb-3">
        <div class="col-md-6">
            <x-kpi-card 
                title="Total Outstanding Receivables" 
                :amount="$totalReceivable" 
                prefix="AED " 
                color="danger" 
                subtitle="Customer balances due" 
                icon="bx-dollar-circle" />
        </div>
        <div class="col-md-6">
            <x-kpi-card 
                title="Customers with Pending Balances" 
                :amount="number_format($customersWithBalance)" 
                prefix="" 
                color="warning" 
                subtitle="Active Clients" 
                icon="bx-user-pin" />
        </div>
    </div>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-5 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Customers</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search customer, company, phone, code...">
            </div>
        </div>
        <div class="col-lg-3 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Balance Status</label>
            <x-searchable-select wire:model.live="filter_balance" class="form-select">
                <option value="all">All Customers</option>
                <option value="with_balance">Pending Balance Only (> 0)</option>
                <option value="zero_balance">Zero Balance</option>
            </x-searchable-select>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="15">15</option>
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

    <!-- Receivables Table Card -->
    <x-table-card target="search, filter_balance, perPage, sortBy, resetFilters" loadingText="Loading receivables..." :paginator="$customers">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <th style="width: 100px;">Code</th>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection">Customer Name / Company</x-th-sort>
                    <th>Mobile / Phone</th>
                    <th>Contact Person</th>
                    <th class="text-end">Credit Limit</th>
                    <x-th-sort field="current_balance" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Outstanding Balance</x-th-sort>
                    <th class="text-center" style="width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td><code>{{ $c->customer_code }}</code></td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $c->name }}</span>
                            @if($c->company_name)
                                <small class="text-muted d-block">{{ $c->company_name }}</small>
                            @endif
                        </td>
                        <td>{{ $c->mobile ?? '-' }}</td>
                        <td>{{ $c->contact_person ?? '-' }}</td>
                        <td class="text-end text-muted font-monospace">AED {{ number_format($c->credit_limit, 2) }}</td>
                        <td class="text-end font-monospace fw-bold font-size-14 {{ $c->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                            AED {{ number_format($c->current_balance, 2) }}
                        </td>
                        <td class="text-center">
                            <button wire:click="viewCustomerInvoices({{ $c->id }})" class="btn btn-sm btn-outline-primary" title="View Unpaid Invoices">
                                <i class="bx bx-list-ul me-1"></i> Invoices
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state 
                                icon="bx bx-check-double" 
                                title="No outstanding receivables found" 
                                message="All customers are currently settled with zero outstanding balance."
                                :search="$search || $filter_balance !== 'all'" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Customer Invoices Modal -->
    @include('livewire.reports.partials.customer-invoices-modal')
</div>
