<x-modal 
    :isOpen="$isModalOpen" 
    title="Process Purchase Return (Debit Note)"
    submitAction="saveReturn"
    saveText="Process Return"
    updateText="Process Return"
    theme="danger">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Return Number</label>
            <input type="text" wire:model="return_number" class="form-control" readonly>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Return Date <span class="text-danger">*</span></label>
            <input type="date" wire:model="return_date" class="form-control @error('return_date') is-invalid @enderror">
            @error('return_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Supplier <span class="text-danger">*</span></label>
        <x-searchable-select wire:model="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" placeholder="Select Supplier...">
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }} (Balance: AED {{ number_format($s->current_balance, 2) }})</option>
            @endforeach
        </x-searchable-select>
        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Product to Return <span class="text-danger">*</span></label>
        <x-searchable-select wire:model.live="product_id" class="form-select @error('product_id') is-invalid @enderror" placeholder="Select Product...">
            @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }} (Stock: {{ number_format($p->current_stock, 2) }})</option>
            @endforeach
        </x-searchable-select>
        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Qty Returned <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model="quantity" class="form-control @error('quantity') is-invalid @enderror">
            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Cost Price (AED) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model="unit_price" class="form-control @error('unit_price') is-invalid @enderror">
            @error('unit_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">VAT %</label>
            <input type="number" step="0.01" wire:model="vat_percent" class="form-control">
        </div>
    </div>

    <div class="mb-2">
        <label class="form-label">Return Reason / Memo</label>
        <textarea wire:model="return_reason" class="form-control" rows="2" placeholder="e.g. Defective merchandise, incorrect specifications"></textarea>
    </div>
</x-modal>
