<div>
    <!-- Page Header -->
    <x-page-header title="Purchase Register & Expense Statement" subtitle="Detailed vendor purchase order logs, VAT input taxes, supplier breakdowns, and cost totals.">
        <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-printer me-1"></i> Print Report
        </button>
    </x-page-header>

    <!-- KPI Summary Metrics -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Total Purchases Spend" 
                :amount="$totalPurchases" 
                prefix="AED " 
                color="primary" 
                :subtitle="'In period (' . $start_date . ' to ' . $end_date . ')'" 
                icon="bx-dollar-circle" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="VAT Input Tax (5%)" 
                :amount="$totalVat" 
                prefix="AED " 
                color="info" 
                subtitle="Recoverable Input Tax" 
                icon="bx-receipt" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Purchase Bills Received" 
                :amount="number_format($totalBills)" 
                prefix="" 
                color="success" 
                subtitle="Confirmed Vendor Invoices" 
                icon="bx-check-double" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Average Purchase Order" 
                :amount="$avgBill" 
                prefix="AED " 
                color="secondary" 
                subtitle="Per Order" 
                icon="bx-calculator" />
        </div>
    </div>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Search Purchases</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search purchase #, supplier...">
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">From Date</label>
            <input type="date" wire:model.live="start_date" class="form-control">
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">To Date</label>
            <input type="date" wire:model.live="end_date" class="form-control">
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Supplier</label>
            <x-searchable-select wire:model.live="supplier_id_filter" class="form-select" placeholder="All Suppliers">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Payment Method</label>
            <x-searchable-select wire:model.live="payment_type_filter" class="form-select" placeholder="All Methods">
                <option value="">All Methods</option>
                <option value="Cash">Cash</option>
                <option value="Bank">Bank</option>
                <option value="Credit">Credit</option>
            </x-searchable-select>
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-1">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="15">15</option>
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

    <!-- Purchase Report Table Card -->
    <x-table-card target="search, start_date, end_date, supplier_id_filter, payment_type_filter, perPage, sortBy, resetFilters" loadingText="Generating purchase report..." :paginator="$purchases">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="purchase_number" :sortField="$sortField" :sortDirection="$sortDirection" width="130px">Purchase #</x-th-sort>
                    <x-th-sort field="purchase_date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th style="min-width: 140px;">Supplier</th>
                    <th>Payment Type</th>
                    <th class="text-end">Subtotal (Excl. VAT)</th>
                    <th class="text-end text-info">VAT Amount (5%)</th>
                    <x-th-sort field="grand_total" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Grand Total (AED)</x-th-sort>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                    <tr>
                        <td><code>{{ $p->purchase_number }}</code></td>
                        <td>{{ $p->purchase_date }}</td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $p->supplier->name ?? 'Supplier' }}</span>
                            @if($p->supplier && $p->supplier->company_name)
                                <small class="text-muted d-block">{{ $p->supplier->company_name }}</small>
                            @endif
                        </td>
                        <td>
                            <x-badge :type="$p->payment_type === 'Cash' ? 'success' : ($p->payment_type === 'Bank' ? 'info' : 'warning')">
                                {{ $p->payment_type }}
                            </x-badge>
                        </td>
                        <td class="text-end text-muted font-monospace">AED {{ number_format($p->subtotal, 2) }}</td>
                        <td class="text-end text-info font-monospace">AED {{ number_format($p->vat_amount, 2) }}</td>
                        <td class="text-end fw-bold text-dark font-monospace font-size-14">AED {{ number_format($p->grand_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state 
                                icon="bx bx-file-blank" 
                                title="No purchase invoices found" 
                                message="No purchase orders match your specified date range or filter criteria."
                                :search="$search || $supplier_id_filter || $payment_type_filter" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>
</div>
