<x-modal 
    :isOpen="$isModalOpen" 
    :title="$isEditMode ? 'Edit Category' : 'Create Product Category'"
    submitAction="saveCategory"
    :isEditMode="$isEditMode"
    saveText="Save Category"
    updateText="Update Category">

    <div class="mb-3">
        <label class="form-label">Category Name <span class="text-danger">*</span></label>
        <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Raw Materials, Electronics">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Short Code (Optional)</label>
        <input type="text" wire:model="code" class="form-control @error('code') is-invalid @enderror" placeholder="e.g. RAW, ELEC">
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-2">
        <label class="form-label">Description</label>
        <textarea wire:model="description" class="form-control" rows="3" placeholder="Optional category description..."></textarea>
    </div>
</x-modal>
