<div>
    <!-- Page Header -->
    <x-page-header title="Income Categories" subtitle="Organize and classify non-sales revenue sources and other income streams.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
            <i class="bx bx-plus me-1"></i> Add Income Category
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-6 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Categories</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search category name, description...">
            </div>
        </div>
        <div class="col-lg-3 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </x-searchable-select>
        </div>
        <div class="col-lg-3 col-md-3">
            <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                <i class="bx bx-reset me-1"></i> Reset
            </button>
        </div>
    </x-filter-card>

    <!-- Categories Data Table Card -->
    <x-table-card target="search, perPage, sortBy, resetFilters" loadingText="Loading categories..." :paginator="$categories">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection">Category Name</x-th-sort>
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
                            <x-badge type="success">Active</x-badge>
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
                            <x-empty-state 
                                icon="bx bx-trending-up" 
                                title="No income categories created yet" 
                                message="Create income categories like Service Income, Rent, Commissions to track miscellaneous revenue."
                                :search="$search"
                                addAction="openModal"
                                addLabel="Add Category" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Category Modal -->
    @include('livewire.income.partials.income-category-modal')
</div>
