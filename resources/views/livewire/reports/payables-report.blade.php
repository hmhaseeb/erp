<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Accounts Payable Aging & Report</h4>
                    <p class="text-muted font-size-13 mb-0">Track outstanding vendor bills and liabilities due for supplier payments.</p>
                </div>
                <div class="page-title-right">
                    <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
                        <i class="bx bx-printer me-1"></i> Print Payables
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title bg-danger-subtle text-danger rounded-circle font-size-20">
                                <i class="bx bx-wallet"></i>
                            </span>
                        </div>
                        <div>
                            <span class="text-muted font-size-12 d-block">Total Outstanding Accounts Payable</span>
                            <h3 class="mb-0 text-danger fw-bold font-monospace">AED {{ number_format($totalPayable, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-20">
                                <i class="bx bx-buildings"></i>
                            </span>
                        </div>
                        <div>
                            <span class="text-muted font-size-12 d-block">Suppliers with Pending Balances</span>
                            <h3 class="mb-0 text-dark fw-bold">{{ number_format($suppliersWithBalance) }} Vendors</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Suppliers</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search supplier, company, phone, code...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Balance Status</label>
                    <select wire:model.live="filter_balance" class="form-select">
                        <option value="all">All Suppliers</option>
                        <option value="with_balance">Pending Balance Only (> 0)</option>
                        <option value="zero_balance">Zero Balance</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                        <i class="bx bx-reset me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payables Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, filter_balance, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading payables...</span>
            </div>

            <div wire:loading.remove wire:target="search, filter_balance, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Code</th>
                            <th wire:click="sortBy('name')" class="sortable">
                                Supplier Name / Company
                                @if($sortField === 'name')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Mobile / Phone</th>
                            <th>Contact Person</th>
                            <th wire:click="sortBy('current_balance')" class="sortable text-end">
                                Outstanding Payable
                                @if($sortField === 'current_balance')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
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
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-check-double"></i>
                                        </div>
                                        <h6 class="text-dark">No outstanding payables found</h6>
                                        <p class="text-muted font-size-13 mb-3">All suppliers and vendor bills are completely settled.</p>
                                        <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                            <i class="bx bx-reset me-1"></i> Reset Filters
                                        </button>
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
                    Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} records
                </div>
                <div>
                    {{ $suppliers->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Bills Modal -->
    @if($selectedSupplier)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Unpaid Purchase Bills — {{ $selectedSupplier->name }}</h5>
                        <button type="button" class="btn-close" wire:click="closePurchasesModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Total Supplier Payable: <strong class="text-danger">AED {{ number_format($selectedSupplier->current_balance, 2) }}</strong></span>
                            <a href="{{ route('payments.supplier') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-plus me-1"></i> Record Payment Voucher
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm font-size-13 mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Purchase #</th>
                                        <th>Date</th>
                                        <th>Grand Total</th>
                                        <th>Paid Amount</th>
                                        <th class="text-end text-danger">Due Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($supplierPurchases as $pur)
                                        <tr>
                                            <td><code>{{ $pur->purchase_number }}</code></td>
                                            <td>{{ $pur->purchase_date }}</td>
                                            <td>AED {{ number_format($pur->grand_total, 2) }}</td>
                                            <td class="text-success">AED {{ number_format($pur->paid_amount, 2) }}</td>
                                            <td class="text-end font-monospace text-danger fw-bold">AED {{ number_format($pur->due_amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-muted">No individual unpaid bills found (balance may stem from opening balance).</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closePurchasesModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
