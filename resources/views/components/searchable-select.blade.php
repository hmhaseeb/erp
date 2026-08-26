@props([
    'placeholder' => 'Select option...',
    'searchPlaceholder' => 'Type to search...'
])

@php
    $attributes = $attributes->merge(['class' => 'form-select']);
    $isInvalid = str_contains($attributes->get('class', ''), 'is-invalid');
@endphp

<div x-data="searchableSelect()" 
     class="position-relative w-100" 
     :style="open ? 'z-index: 1060; position: relative;' : 'position: relative;'"
     x-on:keydown.escape.stop="open = false">

    <!-- Native Select Element (Hidden, retains Livewire & Form bindings) -->
    <select x-ref="nativeSelect" {{ $attributes->merge(['class' => 'd-none']) }}>
        {{ $slot }}
    </select>

    <!-- Custom Searchable Select Trigger Button -->
    <div type="button"
         class="{{ $attributes->get('class') }} cursor-pointer d-flex align-items-center justify-content-between text-start"
         :class="{ 'is-invalid': {{ $isInvalid ? 'true' : 'false' }}, 'focus': open }"
         @click="toggle()"
         style="user-select: none; background-color: #fff;">
        <span class="text-truncate" x-text="selectedLabel || '{{ $placeholder }}'"></span>
        <i class="bx bx-chevron-down ms-2 text-muted font-size-16 transition-icon" :class="{ 'rotate-180': open }"></i>
    </div>

    <!-- Dropdown Menu container -->
    <div x-show="open"
         @click.outside="open = false"
         x-cloak
         class="dropdown-menu show shadow-lg p-2 position-absolute"
         style="top: 100%; left: 0; min-width: 100%; width: max-content; max-width: min(600px, 92vw); z-index: 1070; margin-top: 4px; background: #fff;">
        <div class="input-group input-group-sm mb-2">
            <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
            <input type="text"
                   x-ref="searchInput"
                   x-model="searchQuery"
                   @keydown.arrow-down.prevent="navigateOptions(1)"
                   @keydown.arrow-up.prevent="navigateOptions(-1)"
                   @keydown.enter.prevent="selectFocusedOption()"
                   class="form-control border-start-0"
                   placeholder="{{ $searchPlaceholder }}">
        </div>
        <div class="list-group list-group-flush overflow-auto" style="max-height: 240px;">
            <template x-for="(opt, index) in filteredOptions" :key="index">
                <button type="button"
                        class="list-group-item list-group-item-action py-2 px-2 border-0 rounded font-size-13 text-truncate d-flex align-items-center justify-content-between"
                        :class="{
                            'bg-primary text-white': opt.value == selectedValue,
                            'bg-light text-dark': focusedIndex === index && opt.value != selectedValue,
                            'disabled text-muted': opt.disabled
                        }"
                        @click="selectOption(opt)">
                    <span class="text-truncate" x-text="opt.label"></span>
                    <i x-show="opt.value == selectedValue" class="bx bx-check font-size-16 ms-2 flex-shrink-0"></i>
                </button>
            </template>
            <div x-show="filteredOptions.length === 0" class="p-3 text-center text-muted font-size-12">
                <i class="bx bx-info-circle me-1"></i> No matching options found
            </div>
        </div>
    </div>
</div>
