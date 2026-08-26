@props([
    'field' => '',
    'sortField' => '',
    'sortDirection' => 'asc',
    'width' => null,
    'align' => 'left',
    'class' => '',
])

<th wire:click="sortBy('{{ $field }}')" 
    class="sortable {{ $align === 'right' ? 'text-end' : ($align === 'center' ? 'text-center' : '') }} {{ $class }}" 
    @if($width) style="width: {{ $width }};" @endif>
    {{ $slot }}
    @if($sortField === $field)
        <i class="bx {{ $sortDirection === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }} text-primary"></i>
    @endif
</th>
