<x-modal 
    :isOpen="$isModalOpen" 
    :title="$isEditMode ? 'Edit Supplier Details' : 'Register New Supplier'"
    size="modal-lg modal-dialog-centered"
    submitAction="saveSupplier"
    :isEditMode="$isEditMode"
    saveText="Save Supplier"
    updateText="Update Supplier">

    <!-- 1. Basic & Company Information -->
    <div class="form-section-title">1. Basic & Company Information</div>
    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Supplier Code <span class="text-danger">*</span></label>
            <input type="text" wire:model="supplier_code" class="form-control @error('supplier_code') is-invalid @enderror">
            @error('supplier_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Company / Legal Name</label>
            <input type="text" wire:model="company_name" class="form-control" placeholder="e.g. Apex Electronics Ltd">
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Supplier / Contact Person Name <span class="text-danger">*</span></label>
            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. John Doe">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">TRN / Tax Number</label>
            <input type="text" wire:model="trn_number" class="form-control" placeholder="15-digit TRN">
        </div>
    </div>

    <!-- 2. Contact Details -->
    <div class="form-section-title mt-2">2. Contact Details</div>
    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Mobile Number</label>
            <input type="text" wire:model="mobile" class="form-control" placeholder="+971 50 123 4567">
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" wire:model="email" class="form-control" placeholder="vendor@example.com">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Physical Address</label>
        <textarea wire:model="address" class="form-control" rows="2" placeholder="Warehouse / Office address..."></textarea>
    </div>

    <!-- 3. Balance & Terms -->
    <div class="form-section-title mt-2">3. Opening Balance & Payment Terms</div>
    <div class="row">
        @if(!$isEditMode)
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">Opening Balance (AED)</label>
                <input type="number" step="0.01" wire:model="opening_balance" class="form-control" placeholder="0.00">
            </div>
        @endif
        <div class="{{ $isEditMode ? 'col-12' : 'col-12 col-sm-6' }} mb-3">
            <label class="form-label">Payment Terms</label>
            <input type="text" wire:model="payment_terms" class="form-control" placeholder="e.g. Net 30 Days, Immediate, Cash on Delivery">
        </div>
    </div>

    <div class="mb-2">
        <label class="form-label">Internal Notes</label>
        <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional internal supplier notes..."></textarea>
    </div>
</x-modal>
