<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Account Transaction Entries</h4>
                    <p class="text-muted font-size-13 mb-0">Audit and manage debit and credit ledger postings, cash entries, and fund transfers.</p>
                </div>
                <div class="page-title-right">
                    <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-transfer me-1"></i> New Transaction / Transfer
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
                    <label class="form-label font-size-12 text-muted mb-1">Search Transactions</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search description, type, amount...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Filter Account</label>
                    <select wire:model.live="account_id_filter" class="form-select">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Transaction Type</label>
                    <select wire:model.live="type_filter" class="form-select">
                        <option value="">All Types</option>
                        <option value="Cash In">Cash In</option>
                        <option value="Cash Out">Cash Out</option>
                        <option value="Bank Deposit">Bank Deposit</option>
                        <option value="Bank Withdrawal">Bank Withdrawal</option>
                        <option value="Transfer">Transfer</option>
                        <option value="Sale">Sale Receipt</option>
                        <option value="Purchase">Purchase Payment</option>
                        <option value="Income">Income Entry</option>
                        <option value="Expense">Expense Entry</option>
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
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
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

    <!-- Transactions Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, account_id_filter, type_filter, date_from, date_to, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading transaction entries...</span>
            </div>

            <div wire:loading.remove wire:target="search, account_id_filter, type_filter, date_from, date_to, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('transaction_date')" class="sortable" style="width: 110px;">
                                Date
                                @if($sortField === 'transaction_date')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th wire:click="sortBy('debit')" class="sortable text-end text-success">
                                Debit (Inflow +)
                                @if($sortField === 'debit')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('credit')" class="sortable text-end text-danger">
                                Credit (Outflow -)
                                @if($sortField === 'credit')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr>
                                <td>{{ $t->transaction_date }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $t->account->name ?? 'Account' }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ in_array($t->transaction_type, ['Cash In', 'Bank Deposit', 'Income', 'Sale', 'Customer Payment']) ? 'badge-soft-success' : 'badge-soft-danger' }} font-size-12">
                                        {{ $t->transaction_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 320px;" title="{{ $t->description }}">
                                        {{ $t->description ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-end font-monospace {{ $t->debit > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                                    {{ $t->debit > 0 ? 'AED ' . number_format($t->debit, 2) : '-' }}
                                </td>
                                <td class="text-end font-monospace {{ $t->credit > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    {{ $t->credit > 0 ? 'AED ' . number_format($t->credit, 2) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-transfer"></i>
                                        </div>
                                        @if($search || $account_id_filter || $type_filter || $date_from || $date_to)
                                            <h6 class="text-dark">No transactions found</h6>
                                            <p class="text-muted font-size-13 mb-3">No account entries match your filter criteria.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Reset Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No account transactions logged yet</h6>
                                            <p class="text-muted font-size-13 mb-3">Transactions will be posted automatically as sales, purchases, and income/expenses are recorded.</p>
                                            <button wire:click="openModal" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> New Transaction / Transfer
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
                    Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} records
                </div>
                <div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Transaction / Transfer Modal -->
    @if($isModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Account Transaction / Fund Transfer</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveTransaction">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                <select wire:model.live="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="Cash In">Cash In (Deposit / Receipt)</option>
                                    <option value="Cash Out">Cash Out (Withdrawal / Payment)</option>
                                    <option value="Bank Deposit">Bank Deposit</option>
                                    <option value="Bank Withdrawal">Bank Withdrawal</option>
                                    <option value="Transfer">Account to Account Transfer</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ $type === 'Transfer' ? 'From Source Account' : 'Account' }} <span class="text-danger">*</span></label>
                                <select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror">
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: AED {{ number_format($acc->current_balance, 2) }})</option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if($type === 'Transfer')
                                <div class="mb-3">
                                    <label class="form-label">To Destination Account <span class="text-danger">*</span></label>
                                    <select wire:model="to_account_id" class="form-select @error('to_account_id') is-invalid @enderror">
                                        <option value="">Select Target Account</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: AED {{ number_format($acc->current_balance, 2) }})</option>
                                        @endforeach
                                    </select>
                                    @error('to_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Amount (AED) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Description / Remarks</label>
                                <textarea wire:model="description" class="form-control" rows="2" placeholder="e.g. Bank cash deposit from daily register, fund transfer"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="saveTransaction">Post Transaction</span>
                                <span wire:loading wire:target="saveTransaction"><i class="bx bx-loader-alt bx-spin me-1"></i> Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
