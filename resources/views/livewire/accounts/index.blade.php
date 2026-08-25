<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Cash & Bank Accounts</h4>
                    <p class="text-muted font-size-13 mb-0">Manage company liquid assets, cash drawers, bank accounts, and current balances.</p>
                </div>
                <div class="page-title-right">
                    <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-plus me-1"></i> Add New Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title bg-success-subtle text-success rounded-circle font-size-20">
                                <i class="bx bx-money"></i>
                            </span>
                        </div>
                        <div>
                            <span class="text-muted font-size-12 d-block">Cash Drawers Balance</span>
                            <h4 class="mb-0 text-success fw-bold">AED {{ number_format($totalCash, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-20">
                                <i class="bx bx-buildings"></i>
                            </span>
                        </div>
                        <div>
                            <span class="text-muted font-size-12 d-block">Bank Accounts Balance</span>
                            <h4 class="mb-0 text-primary fw-bold">AED {{ number_format($totalBank, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title bg-info-subtle text-info rounded-circle font-size-20">
                                <i class="bx bx-wallet"></i>
                            </span>
                        </div>
                        <div>
                            <span class="text-muted font-size-12 d-block">Total Liquid Funds</span>
                            <h4 class="mb-0 text-dark fw-bold">AED {{ number_format($totalLiquid, 2) }}</h4>
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
                <div class="col-lg-6 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Accounts</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search account name, bank name, account number...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Account Type</label>
                    <select wire:model.live="type_filter" class="form-select">
                        <option value="">All Types</option>
                        <option value="Cash">Cash Account</option>
                        <option value="Bank">Bank Account</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-2">
                    <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
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

    <!-- Accounts Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, type_filter, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading accounts...</span>
            </div>

            <div wire:loading.remove wire:target="search, type_filter, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('name')" class="sortable">
                                Account Title
                                @if($sortField === 'name')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Type</th>
                            <th>Bank Name</th>
                            <th>Account # / IBAN</th>
                            <th class="text-end">Opening Balance</th>
                            <th wire:click="sortBy('current_balance')" class="sortable text-end">
                                Current Balance
                                @if($sortField === 'current_balance')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Status</th>
                            <th class="text-center" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $acc)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $acc->name }}</span>
                                    @if($acc->notes) <small class="text-muted d-block">{{ $acc->notes }}</small> @endif
                                </td>
                                <td>
                                    <span class="badge {{ $acc->type === 'Cash' ? 'badge-soft-success' : 'badge-soft-primary' }}">
                                        {{ $acc->type }}
                                    </span>
                                </td>
                                <td>{{ $acc->bank_name ?? '-' }}</td>
                                <td><code>{{ $acc->account_number ?? '-' }}</code></td>
                                <td class="text-end text-muted">AED {{ number_format($acc->opening_balance, 2) }}</td>
                                <td class="text-end fw-bold font-size-14 {{ $acc->current_balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    AED {{ number_format($acc->current_balance, 2) }}
                                </td>
                                <td>
                                    <span class="badge {{ $acc->status ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                        {{ $acc->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('accounts.ledger') }}?account_id={{ $acc->id }}" class="btn btn-sm btn-outline-info" title="View Account Ledger">
                                        <i class="bx bx-book-open"></i>
                                    </a>
                                    <button wire:click="editAccount({{ $acc->id }})" class="btn btn-sm btn-outline-primary ms-1" title="Edit Account">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    <button onclick="confirm('Are you sure you want to delete this account?') || event.stopImmediatePropagation()" wire:click="deleteAccount({{ $acc->id }})" class="btn btn-sm btn-outline-danger ms-1" title="Delete Account">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-wallet"></i>
                                        </div>
                                        @if($search || $type_filter)
                                            <h6 class="text-dark">No accounts found</h6>
                                            <p class="text-muted font-size-13 mb-3">No financial accounts match your search filters.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Clear Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No financial accounts set up yet</h6>
                                            <p class="text-muted font-size-13 mb-3">Add cash drawers or bank accounts to start recording collections, disbursements, and expenses.</p>
                                            <button wire:click="openModal" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> Add Account
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
                    Showing {{ $accounts->firstItem() ?? 0 }} to {{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() }} records
                </div>
                <div>
                    {{ $accounts->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Account Modal Form -->
    @if($isModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditMode ? 'Edit Financial Account' : 'Create Cash / Bank Account' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveAccount">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Account Title <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Main Cash Drawer, Emirates NBD Operating">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Account Type <span class="text-danger">*</span></label>
                                <select wire:model.live="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="Cash">Cash Account</option>
                                    <option value="Bank">Bank Account</option>
                                    <option value="Other">Other</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if($type === 'Bank')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" wire:model="bank_name" class="form-control" placeholder="e.g. Emirates NBD, ADCB">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account / IBAN Number</label>
                                        <input type="text" wire:model="account_number" class="form-control" placeholder="Account # or IBAN">
                                    </div>
                                </div>
                            @endif

                            @if(!$isEditMode)
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Opening Balance (AED)</label>
                                        <input type="number" step="0.01" wire:model="opening_balance" class="form-control" placeholder="0.00">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Opening Balance Date <span class="text-danger">*</span></label>
                                        <input type="date" wire:model="opening_balance_date" class="form-control">
                                    </div>
                                </div>
                            @endif

                            <div class="mb-2">
                                <label class="form-label">Description / Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="saveAccount">{{ $isEditMode ? 'Update Account' : 'Save Account' }}</span>
                                <span wire:loading wire:target="saveAccount"><i class="bx bx-loader-alt bx-spin me-1"></i> Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
