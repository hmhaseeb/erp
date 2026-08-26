@props([
    'title' => '',
    'subtitle' => null,
])

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>
                @if($subtitle)
                    <p class="text-muted font-size-13 mb-0">{{ $subtitle }}</p>
                @endif
            </div>
            @if($slot->isNotEmpty())
                <div class="page-title-right">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>
