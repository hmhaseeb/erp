<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Income Transactions</h4>
                    <p class="text-muted font-size-13 mb-0">Record non-sales revenues, services, commissions, and other financial inflows.</p>
                </div>
                <div class="page-title-right">
                    <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-plus me-1"></i> Record Income
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
                    <label class="form-label font-size-12 text-muted mb-1">Search Income</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search description, category, ref...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Category</label>
                    <select wire:model.live="category_id_filter" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Deposit Account</label>
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
                        <option value="12">12</option>
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

    <!-- Income Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, category_id_filter, account_id_filter, date_from, date_to, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading income entries...</span>
            </div>

            <div wire:loading.remove wire:target="search, category_id_filter, account_id_filter, date_from, date_to, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('date')" class="sortable" style="width: 110px;">
                                Date
                                @if($sortField === 'date')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th wire:click="sortBy('amount')" class="sortable text-end">
                                Amount
                                @if($sortField === 'amount')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Reference #</th>
                            <th>Receipt / Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomes as $inc)
                            <tr>
                                <td>{{ $inc->date }}</td>
                                <td>
                                    <span class="badge badge-soft-success font-size-12">{{ $inc->category->name ?? 'Income' }}</span>
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
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-trending-up"></i>
                                        </div>
                                        @if($search || $category_id_filter || $account_id_filter || $date_from || $date_to)
                                            <h6 class="text-dark">No income entries found</h6>
                                            <p class="text-muted font-size-13 mb-3">No records match your filter criteria.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Reset Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No income entries recorded yet</h6>
                                            <p class="text-muted font-size-13 mb-3">Record services, commission, rent, or other income streams.</p>
                                            <button wire:click="openModal" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> Record Income
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
                    Showing {{ $incomes->firstItem() ?? 0 }} to {{ $incomes->lastItem() ?? 0 }} of {{ $incomes->total() }} records
                </div>
                <div>
                    {{ $incomes->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Income Modal Form -->
    @if($isModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Income Entry</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveIncome">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Income Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
                                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Income Category <span class="text-danger">*</span></label>
                                    <select wire:model="income_category_id" class="form-select @error('income_category_id') is-invalid @enderror">
                                        @foreach($categories as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('income_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                    <label class="form-label">Amount (AED) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description / Particulars</label>
                                <input type="text" wire:model="description" class="form-control" placeholder="e.g. IT consulting service fee for client Apex">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Reference Number</label>
                                    <input type="text" wire:model="reference_number" class="form-control" placeholder="Receipt or bank ref #">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Upload Receipt / Invoice (Optional)</label>
                                    <input type="file" wire:model="attachment" class="form-control @error('attachment') is-invalid @enderror">
                                    @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Internal Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <span wire:loading.remove wire:target="saveIncome">Save Income</span>
                                <span wire:loading wire:target="saveIncome"><i class="bx bx-loader-alt bx-spin me-1"></i> Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
