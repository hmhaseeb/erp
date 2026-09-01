<div>
    <!-- Page Header -->
    <x-page-header title="Sales Statement & Tax Report" subtitle="Detailed sales register, VAT tax collections, customer breakdowns, and invoice statements.">
        <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-printer me-1"></i> Print Report
        </button>
    </x-page-header>

    <!-- KPI Summary Metrics -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Total Sales Revenue" 
                :amount="$totalSales" 
                prefix="AED " 
                color="success" 
                :subtitle="'In period (' . $start_date . ' to ' . $end_date . ')'" 
                icon="bx-dollar-circle" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="VAT Output Tax (5%)" 
                :amount="$totalVat" 
                prefix="AED " 
                color="info" 
                subtitle="Payable Tax Collected" 
                icon="bx-receipt" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Invoices Issued" 
                :amount="number_format($totalInvoices)" 
                prefix="" 
                color="primary" 
                subtitle="Confirmed Sales" 
                icon="bx-check-double" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Average Invoice Value" 
                :amount="$avgInvoice" 
                prefix="AED " 
                color="secondary" 
                subtitle="Per Transaction" 
                icon="bx-calculator" />
        </div>
    </div>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Search Sales</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search invoice #, customer...">
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
            <label class="form-label font-size-12 text-muted mb-1">Customer</label>
            <x-searchable-select wire:model.live="customer_id_filter" class="form-select" placeholder="All Customers">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
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

    <!-- Sales Report Table Card -->
    <x-table-card target="search, start_date, end_date, customer_id_filter, payment_type_filter, perPage, sortBy, resetFilters" loadingText="Generating sales statement..." :paginator="$sales">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="invoice_number" :sortField="$sortField" :sortDirection="$sortDirection" width="130px">Invoice #</x-th-sort>
                    <x-th-sort field="sale_date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th style="min-width: 140px;">Customer</th>
                    <th>Payment Type</th>
                    <th class="text-end">Subtotal (Excl. VAT)</th>
                    <th class="text-end text-info">VAT Amount (5%)</th>
                    <x-th-sort field="grand_total" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Grand Total (AED)</x-th-sort>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                    <tr>
                        <td><code>{{ $s->invoice_number }}</code></td>
                        <td>{{ $s->sale_date }}</td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $s->customer->name ?? 'Walk-in Customer' }}</span>
                        </td>
                        <td>
                            <x-badge :type="$s->payment_type === 'Cash' ? 'success' : ($s->payment_type === 'Bank' ? 'info' : 'warning')">
                                {{ $s->payment_type }}
                            </x-badge>
                        </td>
                        <td class="text-end text-muted font-monospace">AED {{ number_format($s->subtotal, 2) }}</td>
                        <td class="text-end text-info font-monospace">AED {{ number_format($s->vat_amount, 2) }}</td>
                        <td class="text-end fw-bold text-success font-monospace font-size-14">AED {{ number_format($s->grand_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state 
                                icon="bx bx-file-blank" 
                                title="No sales invoices found" 
                                message="No sales records match your specified date range or filter criteria."
                                :search="$search || $customer_id_filter || $payment_type_filter" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>
</div>
