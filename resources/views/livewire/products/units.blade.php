<div>
    <!-- Page Header -->
    <x-page-header title="Units of Measure" subtitle="Manage measurement units (PCS, KG, BOX, METER, etc.) for product items.">
        <button wire:click="openModal" class="btn btn-primary waves-effect waves-light">
            <i class="bx bx-plus me-1"></i> Add New Unit
        </button>
    </x-page-header>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-lg-6 col-md-6">
            <label class="form-label font-size-12 text-muted mb-1">Search Units</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search unit name or abbreviation code...">
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

    <!-- Units Data Table Card -->
    <x-table-card target="search, perPage, sortBy, resetFilters" loadingText="Loading units..." :paginator="$units">
        <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <x-th-sort field="name" :sortField="$sortField" :sortDirection="$sortDirection">Unit Name</x-th-sort>
                    <x-th-sort field="code" :sortField="$sortField" :sortDirection="$sortDirection" width="200px">Short Code / Abbreviation</x-th-sort>
                    <th class="text-center" style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $u)
                    <tr>
                        <td>
                            <span class="fw-semibold text-dark">{{ $u->name }}</span>
                        </td>
                        <td>
                            <x-badge type="primary" size="font-size-12 px-3 py-1">{{ $u->code }}</x-badge>
                        </td>
                        <td class="text-center">
                            <button wire:click="editUnit({{ $u->id }})" class="btn btn-sm btn-outline-primary" title="Edit Unit">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <button onclick="confirm('Are you sure you want to delete this unit?') || event.stopImmediatePropagation()" wire:click="deleteUnit({{ $u->id }})" class="btn btn-sm btn-outline-danger ms-1" title="Delete Unit">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <x-empty-state 
                                icon="bx bx-ruler" 
                                title="No units configured" 
                                message="Add measurement units like PCS, KG, BOX to use in product inventory."
                                :search="$search"
                                addAction="openModal"
                                addLabel="Add New Unit" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table-card>

    <!-- Unit Modal -->
    @include('livewire.products.partials.unit-modal')
</div>
