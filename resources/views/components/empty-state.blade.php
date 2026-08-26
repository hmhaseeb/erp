@props([
    'icon' => 'bx bx-info-circle',
    'title' => 'No records found',
    'message' => null,
    'search' => null,
    'resetAction' => 'resetFilters',
    'addAction' => null,
    'addLabel' => 'Add New',
])

<div class="empty-state">
    <div class="empty-state-icon">
        <i class="{{ $icon }}"></i>
    </div>
    @if($search)
        <h6 class="text-dark">No matching records found</h6>
        <p class="text-muted font-size-13 mb-3">No results match your search query or active filters.</p>
        <button type="button" wire:click="{{ $resetAction }}" class="btn btn-sm btn-light">
            <i class="bx bx-reset me-1"></i> Clear Search / Filters
        </button>
    @else
        <h6 class="text-dark">{{ $title }}</h6>
        @if($message)
            <p class="text-muted font-size-13 mb-3">{{ $message }}</p>
        @endif
        @if($addAction)
            <button type="button" wire:click="{{ $addAction }}" class="btn btn-sm btn-primary">
                <i class="bx bx-plus me-1"></i> {{ $addLabel }}
            </button>
        @endif
        {{ $slot }}
    @endif
</div>
