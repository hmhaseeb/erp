@props([
    'target' => 'search, perPage, sortBy, resetFilters',
    'loadingText' => 'Loading data...',
    'paginator' => null,
    'header' => null,
])

<div class="card border-0 shadow-sm">
    @if(isset($header))
        <div class="card-header bg-white border-bottom py-3">
            {{ $header }}
        </div>
    @endif
    <div class="card-body p-0">
        @if($target)
            <div wire:loading.flex wire:target="{{ $target }}" class="justify-content-center align-items-center py-4 text-primary">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span class="font-size-13">{{ $loadingText }}</span>
            </div>
            <div wire:loading.remove wire:target="{{ $target }}" class="table-responsive">
                {{ $slot }}
            </div>
        @else
            <div class="table-responsive">
                {{ $slot }}
            </div>
        @endif

        @if($paginator && method_exists($paginator, 'links') && $paginator->hasPages())
            <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted font-size-13 mb-2 mb-sm-0">
                    Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} records
                </div>
                <div>
                    {{ $paginator->links() }}
                </div>
            </div>
        @elseif($paginator && method_exists($paginator, 'total'))
            <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted font-size-13 mb-2 mb-sm-0">
                    Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} records
                </div>
                <div>
                    {{ $paginator->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
