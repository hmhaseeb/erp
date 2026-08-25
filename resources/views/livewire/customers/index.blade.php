<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Customers Directory</h4>
                    <p class="text-muted font-size-13 mb-0">Manage customer accounts, credit limits, receivables, and contact details.</p>
                </div>
                <div class="page-title-right">
                    <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-plus me-1"></i> Register Customer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Customers</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by name, company, code, phone, email...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Balance Status</label>
                    <select wire:model.live="balance_filter" class="form-select">
                        <option value="">All Balances</option>
                        <option value="has_balance">Outstanding Receivable (&gt; 0)</option>
                        <option value="zero_balance">Settled (0.00)</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Status</label>
                    <select wire:model.live="status_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-2">
                    <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
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

    <!-- Customers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, status_filter, balance_filter, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading customers...</span>
            </div>

            <div wire:loading.remove wire:target="search, status_filter, balance_filter, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('customer_code')" class="sortable" style="width: 110px;">
                                Code
                                @if($sortField === 'customer_code')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('name')" class="sortable">
                                Customer / Contact
                                @if($sortField === 'name')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('company_name')" class="sortable">
                                Company
                                @if($sortField === 'company_name')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Contact Information</th>
                            <th>Credit Limit</th>
                            <th wire:click="sortBy('current_balance')" class="sortable text-end">
                                Receivable Due
                                @if($sortField === 'current_balance')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Status</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                            <tr>
                                <td><code>{{ $c->customer_code }}</code></td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $c->name }}</span>
                                    @if($c->contact_person)
                                        <small class="text-muted d-block"><i class="bx bx-user font-size-11"></i> {{ $c->contact_person }}</small>
                                    @endif
                                </td>
                                <td>{{ $c->company_name ?? '-' }}</td>
                                <td>
                                    @if($c->mobile) <div><i class="bx bx-phone font-size-12 text-muted me-1"></i> {{ $c->mobile }}</div> @endif
                                    @if($c->email) <div class="text-muted"><i class="bx bx-envelope font-size-12 text-muted me-1"></i> {{ $c->email }}</div> @endif
                                </td>
                                <td>AED {{ number_format($c->credit_limit, 2) }}</td>
                                <td class="text-end">
                                    <span class="fw-bold {{ $c->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                        AED {{ number_format($c->current_balance, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $c->status ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                        {{ $c->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="editCustomer({{ $c->id }})" class="btn btn-sm btn-outline-primary" title="Edit Customer">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    <button onclick="confirm('Are you sure you want to delete this customer?') || event.stopImmediatePropagation()" wire:click="deleteCustomer({{ $c->id }})" class="btn btn-sm btn-outline-danger ms-1" title="Delete Customer">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-users"></i>
                                        </div>
                                        @if($search || $status_filter !== '' || $balance_filter)
                                            <h6 class="text-dark">No customers found</h6>
                                            <p class="text-muted font-size-13 mb-3">No customers match your current filters.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Clear Filters
                                            </button>
                                        @else
                                            <h6 class="text-dark">No customers registered yet</h6>
                                            <p class="text-muted font-size-13 mb-3">Register your clients and customers to begin issuing sales invoices and tracking receivables.</p>
                                            <button wire:click="openModal" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> Register Customer
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
                    Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} records
                </div>
                <div>
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Registration / Edit Modal -->
    @if($isModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditMode ? 'Edit Customer Details' : 'Register New Customer' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveCustomer">
                        <div class="modal-body">
                            <!-- 1. Basic & Company Information -->
                            <div class="form-section-title">1. Basic & Company Information</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Customer Code <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="customer_code" class="form-control @error('customer_code') is-invalid @enderror">
                                    @error('customer_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Company Name (If Business Client)</label>
                                    <input type="text" wire:model="company_name" class="form-control" placeholder="e.g. Modern Trading LLC">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Customer / Primary Contact Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Jane Smith">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">TRN / Tax Number</label>
                                    <input type="text" wire:model="trn_number" class="form-control" placeholder="15-digit TRN">
                                </div>
                            </div>

                            <!-- 2. Contact Details -->
                            <div class="form-section-title mt-2">2. Contact Details</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" wire:model="mobile" class="form-control" placeholder="+971 50 987 6543">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" wire:model="email" class="form-control" placeholder="client@example.com">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Delivery / Billing Address</label>
                                <textarea wire:model="address" class="form-control" rows="2" placeholder="Office / Warehouse address..."></textarea>
                            </div>

                            <!-- 3. Financial & Credit Terms -->
                            <div class="form-section-title mt-2">3. Credit Limit & Terms</div>
                            <div class="row">
                                @if(!$isEditMode)
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Opening Receivable (AED)</label>
                                        <input type="number" step="0.01" wire:model="opening_balance" class="form-control" placeholder="0.00">
                                    </div>
                                @endif
                                <div class="{{ $isEditMode ? 'col-md-6' : 'col-md-4' }} mb-3">
                                    <label class="form-label">Credit Limit (AED)</label>
                                    <input type="number" step="0.01" wire:model="credit_limit" class="form-control" placeholder="0.00">
                                </div>
                                <div class="{{ $isEditMode ? 'col-md-6' : 'col-md-4' }} mb-3">
                                    <label class="form-label">Payment Terms</label>
                                    <input type="text" wire:model="payment_terms" class="form-control" placeholder="e.g. Net 15 Days, Cash, Due on receipt">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Internal Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes regarding this customer..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="saveCustomer">{{ $isEditMode ? 'Update Customer' : 'Save Customer' }}</span>
                                <span wire:loading wire:target="saveCustomer"><i class="bx bx-loader-alt bx-spin me-1"></i> Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
