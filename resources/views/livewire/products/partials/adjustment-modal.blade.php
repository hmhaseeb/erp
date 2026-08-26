<x-modal 
    :isOpen="$isModalOpen" 
    title="Record Stock Adjustment"
    submitAction="saveAdjustment"
    saveText="Apply Adjustment"
    updateText="Apply Adjustment">

    <div class="mb-3">
        <label class="form-label">Adjustment Date <span class="text-danger">*</span></label>
        <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Select Product <span class="text-danger">*</span></label>
        <x-searchable-select wire:model.live="product_id" class="form-select @error('product_id') is-invalid @enderror" placeholder="Select Product...">
            @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }} (Current Stock: {{ number_format($p->current_stock, 2) }})</option>
            @endforeach
        </x-searchable-select>
        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
            <x-searchable-select wire:model="movement_type" class="form-select">
                <option value="ADJUSTMENT_IN">Stock In (+) / Increase</option>
                <option value="ADJUSTMENT_OUT">Stock Out (-) / Damaged / Decrease</option>
            </x-searchable-select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Quantity <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model="quantity" class="form-control @error('quantity') is-invalid @enderror">
            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Unit Cost Price (AED) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" wire:model="unit_cost" class="form-control @error('unit_cost') is-invalid @enderror">
        @error('unit_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-2">
        <label class="form-label">Reason / Notes</label>
        <textarea wire:model="notes" class="form-control" rows="2" placeholder="e.g. Physical inventory count correction, damaged goods write-off"></textarea>
    </div>
</x-modal>
