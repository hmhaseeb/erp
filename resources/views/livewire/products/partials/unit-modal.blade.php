<x-modal 
    :isOpen="$isModalOpen" 
    :title="$isEditMode ? 'Edit Measurement Unit' : 'Add Measurement Unit'"
    submitAction="saveUnit"
    :isEditMode="$isEditMode"
    saveText="Save Unit"
    updateText="Update Unit">

    <div class="mb-3">
        <label class="form-label">Unit Full Name <span class="text-danger">*</span></label>
        <input type="text" wire:model="name" class="form-control text-uppercase @error('name') is-invalid @enderror" placeholder="e.g. PIECES, KILOGRAMS, BOXES">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Unit Code / Abbreviation</label>
        <input type="text" wire:model="code" class="form-control text-uppercase @error('code') is-invalid @enderror" placeholder="e.g. PCS, KG, BOX">
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</x-modal>
