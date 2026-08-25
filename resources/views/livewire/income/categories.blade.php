<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Income Categories</h4>
                    <p class="text-muted font-size-13 mb-0">Organize and classify non-sales revenue sources and other income streams.</p>
                </div>
                <div class="page-title-right">
                    <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
                        <i class="bx bx-plus me-1"></i> Add Income Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-6 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Categories</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search category name, description...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-3">
                    <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                        <i class="bx bx-reset me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div wire:loading.flex wire:target="search, perPage, sortBy, resetFilters" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">Loading categories...</span>
            </div>

            <div wire:loading.remove wire:target="search, perPage, sortBy, resetFilters" class="table-responsive">
                <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('name')" class="sortable">
                                Category Name
                                @if($sortField === 'name')
                                    <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
                                @endif
                            </th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $c)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $c->name }}</span>
                                </td>
                                <td>{{ $c->description ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-soft-success font-size-12">Active</span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="editCategory({{ $c->id }})" class="btn btn-sm btn-outline-primary" title="Edit Category">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    <button onclick="confirm('Are you sure you want to delete this category?') || event.stopImmediatePropagation()" wire:click="deleteCategory({{ $c->id }})" class="btn btn-sm btn-outline-danger ms-1" title="Delete Category">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-trending-up"></i>
                                        </div>
                                        @if($search)
                                            <h6 class="text-dark">No matching income categories found</h6>
                                            <p class="text-muted font-size-13 mb-3">No categories match your search query.</p>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-light">
                                                <i class="bx bx-reset me-1"></i> Clear Search
                                            </button>
                                        @else
                                            <h6 class="text-dark">No income categories created yet</h6>
                                            <p class="text-muted font-size-13 mb-3">Create income categories like Service Income, Rent, Commissions to track miscellaneous revenue.</p>
                                            <button wire:click="openModal" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> Add Category
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
                    Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} records
                </div>
                <div>
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    @if($isModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditMode ? 'Edit Income Category' : 'Create Income Category' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveCategory">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Service Income, Rental Income">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description" class="form-control" rows="3" placeholder="Optional category description..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="saveCategory">{{ $isEditMode ? 'Update Category' : 'Save Category' }}</span>
                                <span wire:loading wire:target="saveCategory"><i class="bx bx-loader-alt bx-spin me-1"></i> Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
