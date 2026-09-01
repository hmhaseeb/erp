<div>
    <!-- Page Header -->
    <x-page-header title="Product Categories" subtitle="Organize products into classification groups and categories.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-plus me-1"></i> Add Category
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-8 col-lg-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Categories</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search by category name, code, description...">
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
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

    <!-- Categories Data Table Card -->
    <x-table-card target="search, perPage, sortBy, resetFilters" loadingText="Loading categories..." :paginator="$categories">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="code" :sortField="$sortField" :sortDirection="$sortDirection" width="120px">Code</x-th-sort>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection" style="min-width: 140px;">Category Name</x-th-sort>
                    <th style="min-width: 140px;">Description</th>
                    <th class="text-center" style="width: 140px;">Assigned Products</th>
                    <th class="text-center text-nowrap" style="min-width: 90px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $c)
                    <tr>
                        <td><code>{{ $c->code ?? '-' }}</code></td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $c->name }}</span>
                        </td>
                        <td>{{ $c->description ?? '-' }}</td>
                        <td class="text-center">
                            <x-badge type="primary">{{ $c->products_count }} Products</x-badge>
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
                        <td colspan="5">
                            <x-empty-state 
                                icon="bx bx-category" 
                                title="No product categories created yet" 
                                message="Create product categories to organize and filter inventory items."
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
    @include('livewire.products.partials.category-modal')
</div>
