<div>
    <!-- Page Header -->
    <x-page-header title="Accounts Payable Aging & Report" subtitle="Track outstanding vendor bills and liabilities due for supplier payments.">
        <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
            <i class="bx bx-printer me-1"></i> Print Payables
        </button>
    </x-page-header>

    <!-- Summary KPI Cards -->
    <div class="row mb-3">
        <div class="col-md-6">
            <x-kpi-card 
                title="Total Outstanding Accounts Payable" 
                :amount="$totalPayable" 
                prefix="AED " 
                color="danger" 
                subtitle="Vendor balances due" 
                icon="bx-wallet" />
        </div>
        <div class="col-md-6">
            <x-kpi-card 
                title="Suppliers with Pending Balances" 
                :amount="number_format($suppliersWithBalance)" 
                prefix="" 
                color="primary" 
                subtitle="Active Vendors" 
                icon="bx-buildings" />
        </div>
    </div>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-5 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Suppliers</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search supplier, company, phone, code...">
            </div>
        </div>
        <div class="col-lg-3 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Balance Status</label>
            <x-searchable-select wire:model.live="filter_balance" class="form-select">
                <option value="all">All Suppliers</option>
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

    <!-- Payables Table Card -->
    <x-table-card target="search, filter_balance, perPage, sortBy, resetFilters" loadingText="Loading payables..." :paginator="$suppliers">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <th style="width: 100px;">Code</th>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection">Supplier Name / Company</x-th-sort>
                    <th>Mobile / Phone</th>
                    <th>Contact Person</th>
                    <x-th-sort field="current_balance" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Outstanding Payable</x-th-sort>
                    <th class="text-center" style="width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $s)
                    <tr>
                        <td><code>{{ $s->supplier_code }}</code></td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $s->name }}</span>
                            @if($s->company_name)
                                <small class="text-muted d-block">{{ $s->company_name }}</small>
                            @endif
                        </td>
                        <td>{{ $s->mobile ?? '-' }}</td>
                        <td>{{ $s->contact_person ?? '-' }}</td>
                        <td class="text-end font-monospace fw-bold font-size-14 {{ $s->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                            AED {{ number_format($s->current_balance, 2) }}
                        </td>
                        <td class="text-center">
                            <button wire:click="viewSupplierPurchases({{ $s->id }})" class="btn btn-sm btn-outline-primary" title="View Unpaid Bills">
                                <i class="bx bx-list-ul me-1"></i> Bills
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-empty-state 
                                icon="bx bx-check-double" 
                                title="No outstanding payables found" 
                                message="All suppliers and vendor bills are completely settled."
                                :search="$search || $filter_balance !== 'all'" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Supplier Bills Modal -->
    @include('livewire.reports.partials.supplier-bills-modal')
</div>
