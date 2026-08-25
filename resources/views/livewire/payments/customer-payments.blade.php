<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Customer Receipts & Payments</h4>
                    <p class="text-muted font-size-13 mb-0">Record money received from customers, bank/cash deposits, and reconcile unpaid sales invoices.</p>
                </div>
                <div class="page-title-right">
                    <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-plus me-1"></i> Record Customer Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Receipts</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search receipt #, customer, ref...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Filter Customer</label>
                    <select wire:model.live="customer_id_filter" class="form-select">
                        <option value="">All Customers</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Receiving Account</label>
                    <select wire:model.live="account_id_filter" class="form-select">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
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
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-end">
                    <button type="button" wire:click="resetFilters" class="btn btn-sm btn-light">
                        <i class="bx bx-reset me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, customer_id_filter, account_id_filter, date_from, date_to, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading receipt vouchers...</span>
            </div>

            <div wire:loading.remove wire:target="search, customer_id_filter, account_id_filter, date_from, date_to, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('payment_number')" class="sortable" style="width: 130px;">
                                Receipt #
                                @if($sortField === 'payment_number')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('payment_date')" class="sortable" style="width: 110px;">
                                Date
                                @if($sortField === 'payment_date')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Customer</th>
                            <th>Deposited Account</th>
                            <th wire:click="sortBy('amount')" class="sortable text-end">
                                Amount Received
                                @if($sortField === 'amount')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Reference / Cheque #</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr>
                                <td><code>{{ $p->payment_number }}</code></td>
                                <td>{{ $p->payment_date }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $p->customer->name ?? 'Walk-in' }}</span>
                                    @if($p->customer && $p->customer->company_name)
                                        <small class="text-muted d-block">{{ $p->customer->company_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-soft-info">{{ $p->account->name ?? 'Default Cash' }}</span>
                                </td>
                                <td class="text-end fw-bold text-success font-size-14">
                                    AED {{ number_format($p->amount, 2) }}
                                </td>
                                <td>{{ $p->reference_number ?? '-' }}</td>
                                <td><span class="text-muted font-size-12">{{ $p->notes ?? '-' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-receipt"></i>
                                        </div>
                                        @if($search || $customer_id_filter || $account_id_filter || $date_from || $date_to)
                                            <h6 class="text-dark">No customer payment vouchers found</h6>
                                            <p class="text-muted font-size-13 mb-3">No receipts match your search filters.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Reset Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No customer payments recorded</h6>
                                            <p class="text-muted font-size-13 mb-3">Record customer receipts to reconcile outstanding balances and credit cash/bank accounts.</p>
                                            <button wire:click="openModal" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> Record Customer Receipt
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted font-size-13 mb-2 mb-sm-0">
                    Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} records
                </div>
                <div>
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Payment Modal Form -->
    @if($isModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Customer Payment Receipt</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="savePayment">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="payment_date" class="form-control @error('payment_date') is-invalid @enderror">
                                    @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                                    <select wire:model.live="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} (Receivable: AED {{ number_format($c->current_balance, 2) }})</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deposit Into Account <span class="text-danger">*</span></label>
                                    <select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror">
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: AED {{ number_format($acc->current_balance, 2) }})</option>
                                        @endforeach
                                    </select>
                                    @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Total Amount Received (AED) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cheque / Reference Number</label>
                                    <input type="text" wire:model="reference_number" class="form-control" placeholder="Cheque # or transaction reference">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Notes / Remarks</label>
                                    <input type="text" wire:model="notes" class="form-control" placeholder="Payment remarks...">
                                </div>
                            </div>

                            @if(count($unpaidSales) > 0)
                                <div class="mt-2">
                                    <label class="form-label fw-bold">Allocate to Unpaid Invoices (Optional):</label>
                                    <div class="table-responsive" style="max-height: 200px;">
                                        <table class="table table-sm table-bordered font-size-12 mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Invoice #</th>
                                                    <th>Date</th>
                                                    <th class="text-end">Due Amount</th>
                                                    <th style="width: 130px;" class="text-end">Allocate (AED)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($unpaidSales as $sale)
                                                    <tr>
                                                        <td><code>{{ $sale->invoice_number }}</code></td>
                                                        <td>{{ $sale->sale_date }}</td>
                                                        <td class="text-end font-monospace text-danger">AED {{ number_format($sale->due_amount, 2) }}</td>
                                                        <td>
                                                            <input type="number" step="0.01" wire:model="allocations.{{ $sale->id }}" class="form-control form-control-sm text-end" placeholder="0.00">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <span wire:loading.remove wire:target="savePayment">Save Receipt</span>
                                <span wire:loading wire:target="savePayment"><i class="bx bx-loader-alt bx-spin me-1"></i> Recording...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
