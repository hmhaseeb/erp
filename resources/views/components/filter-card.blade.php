@props([
    'extra' => null,
])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            {{ $slot }}
        </div>
        @if(isset($extra))
            <div class="row mt-2">
                {{ $extra }}
            </div>
        @endif
    </div>
</div>
