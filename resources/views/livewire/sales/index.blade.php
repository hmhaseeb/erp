<div>
    <!-- Page Header -->
    <x-page-header title="Sales Invoices" subtitle="Issue customer invoices, track collections, view tax statements, and generate PDF invoices.">
        <a href="{{ route('sales.create') }}" class="btn btn-success waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-plus me-1"></i> New Sales Invoice
        </a>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Search Sales</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by Invoice #, customer...">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Filter Customer</label>
            <x-searchable-select wire:model.live="customer_id_filter" class="form-select" placeholder="All Customers">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
            <label class="form-label font-size-12 text-muted mb-1">Payment Method</label>
            <x-searchable-select wire:model.live="payment_type_filter" class="form-select" placeholder="All Methods">
                <option value="">All Methods</option>
                <option value="Cash">Cash</option>
                <option value="Bank">Bank / Card</option>
                <option value="Credit">Credit (Receivable)</option>
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

    <!-- Sales Data Table Card -->
    <x-table-card target="search, customer_id_filter, payment_type_filter, status_filter, date_from, date_to, perPage, sortBy, resetFilters" loadingText="Loading sales invoices..." :paginator="$sales">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="invoice_number" :sortField="$sortField" :sortDirection="$sortDirection" width="130px">Invoice #</x-th-sort>
                    <x-th-sort field="sale_date" :sortField="$sortField" :sortDirection="$sortDirection" width="110px">Date</x-th-sort>
                    <th>Customer</th>
                    <th>Payment Type</th>
                    <x-th-sort field="grand_total" :sortField="$sortField" :sortDirection="$sortDirection" align="right">Grand Total</x-th-sort>
                    <th class="text-end">Paid Amount</th>
                    <th class="text-end">Due Balance</th>
                    <th>Status</th>
                    <th class="text-center" style="min-width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td><code>{{ $sale->invoice_number }}</code></td>
                        <td>{{ $sale->sale_date }}</td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $sale->customer->name ?? 'Walk-in Customer' }}</span>
                            @if($sale->customer && $sale->customer->company_name)
                                <small class="text-muted d-block font-size-11">{{ $sale->customer->company_name }}</small>
                            @endif
                        </td>
                        <td>
                            <x-badge :type="$sale->payment_type === 'Cash' ? 'success' : ($sale->payment_type === 'Bank' ? 'info' : 'warning')">
                                {{ $sale->payment_type }}
                            </x-badge>
                        </td>
                        <td class="text-end fw-bold text-dark font-monospace">AED {{ number_format($sale->grand_total, 2) }}</td>
                        <td class="text-end text-success font-monospace">AED {{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="text-end font-monospace {{ $sale->due_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                            AED {{ number_format($sale->due_amount, 2) }}
                        </td>
                        <td>
                            <x-badge :type="$sale->status === 'Confirmed' ? 'success' : ($sale->status === 'Cancelled' ? 'danger' : 'warning')">
                                {{ $sale->status }}
                            </x-badge>
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="d-inline-flex gap-1">
                                <button wire:click="viewDetails({{ $sale->id }})" class="btn btn-sm btn-outline-primary" title="View Invoice">
                                    <i class="bx bx-show me-1"></i> View
                                </button>
                                <a href="{{ route('sales.pdf', ['id' => $sale->id]) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Download PDF">
                                    <i class="bx bxs-file-pdf"></i> PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <x-empty-state 
                                icon="bx bx-shopping-bag" 
                                title="No sales invoices found" 
                                message="Create your first sales invoice to sell inventory, record revenue, and issue receipts."
                                :search="$search || $customer_id_filter || $payment_type_filter || $date_from || $date_to">
                                <a href="{{ route('sales.create') }}" class="btn btn-sm btn-success">
                                    <i class="bx bx-plus me-1"></i> New Sales Invoice
                                </a>
                            </x-empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Sales Details Modal (Lazy Loaded) -->
    @if($selectedSale)
        @include('livewire.sales.partials.sale-details-modal')
    @endif
</div>
