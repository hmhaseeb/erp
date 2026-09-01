<x-modal 
    :isOpen="$isSupplierModalOpen" 
    title="Register New Supplier"
    size="modal-lg modal-dialog-centered modal-dialog-scrollable"
    submitAction="saveNewSupplier"
    closeAction="closeSupplierModal"
    saveText="Save & Select Supplier"
    savingText="Registering Supplier..."
    theme="primary">

    <!-- 1. Basic & Company Information -->
    <div class="form-section-title">1. Basic & Company Information</div>
    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Supplier Code <span class="text-danger">*</span></label>
            <input type="text" wire:model="supp_code" class="form-control @error('supp_code') is-invalid @enderror">
            @error('supp_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Company / Legal Name</label>
            <input type="text" wire:model="supp_company_name" class="form-control" placeholder="e.g. Apex Electronics Ltd">
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Supplier / Contact Person Name <span class="text-danger">*</span></label>
            <input type="text" wire:model="supp_name" class="form-control @error('supp_name') is-invalid @enderror" placeholder="e.g. John Doe">
            @error('supp_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">TRN / Tax Number</label>
            <input type="text" wire:model="supp_trn_number" class="form-control" placeholder="15-digit TRN">
        </div>
    </div>

    <!-- 2. Contact Details -->
    <div class="form-section-title mt-2">2. Contact Details</div>
    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Mobile Number</label>
            <input type="text" wire:model="supp_mobile" class="form-control" placeholder="+971 50 123 4567">
            @error('supp_mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Email Address</label>
            <input type="email" wire:model="supp_email" class="form-control" placeholder="vendor@example.com">
            @error('supp_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label font-size-12">Physical Address</label>
        <textarea wire:model="supp_address" class="form-control" rows="2" placeholder="Warehouse / Office address..."></textarea>
    </div>

    <!-- 3. Balance & Terms -->
    <div class="form-section-title mt-2">3. Opening Balance & Payment Terms</div>
    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Opening Balance (AED)</label>
            <input type="number" step="0.01" wire:model="supp_opening_balance" class="form-control" placeholder="0.00">
            @error('supp_opening_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Payment Terms (Days)</label>
            <input type="text" wire:model="supp_payment_terms" class="form-control" placeholder="e.g. 30 Days Net">
        </div>
    </div>

    <div class="mb-2">
        <label class="form-label font-size-12">Internal Notes</label>
        <textarea wire:model="supp_notes" class="form-control" rows="2" placeholder="Private internal vendor notes..."></textarea>
    </div>
</x-modal>
