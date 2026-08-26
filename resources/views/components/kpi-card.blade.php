@props([
    'title' => '',
    'value' => null,
    'amount' => null,
    'subtitle' => null,
    'icon' => null,
    'color' => 'primary',
    'col' => null,
    'prefix' => '',
])

@php
    $displayValue = $value ?? ($amount !== null ? number_format((float)$amount, 2) : '0.00');
    $iconClass = $icon ? (str_contains($icon, 'bx') ? (str_starts_with($icon, 'bx ') ? $icon : 'bx ' . $icon) : 'bx ' . $icon) : 'bx bx-bar-chart-alt-2';
@endphp

@if($col)
<div class="{{ $col }}">
@endif

    <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="overflow-hidden me-2">
                    <span class="text-muted mb-2 lh-1 d-block text-truncate font-size-12 fw-semibold text-uppercase letter-spacing-1">{{ $title }}</span>
                    <h4 class="mb-0 text-{{ $color }} fw-bold font-monospace font-size-19 text-nowrap">
                        {{ $prefix }}{{ $displayValue }}
                    </h4>
                    @if($subtitle)
                        <small class="text-muted font-size-11 d-block mt-1 text-truncate">{{ $subtitle }}</small>
                    @endif
                </div>
                <div class="avatar-sm flex-shrink-0 ms-2">
                    <span class="avatar-title rounded-circle font-size-22 bg-light text-{{ $color }}"
                          style="background-color: rgba(var(--bs-{{ $color }}-rgb, 85, 110, 230), 0.12) !important; color: var(--bs-{{ $color }}, #556ee6) !important;">
                        <i class="{{ $iconClass }}"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

@if($col)
</div>
@endif
