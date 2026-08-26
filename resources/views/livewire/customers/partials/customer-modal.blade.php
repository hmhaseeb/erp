<x-modal 
    :isOpen="$isModalOpen" 
    :title="$isEditMode ? 'Edit Customer Details' : 'Register New Customer'"
    size="modal-lg modal-dialog-centered"
    submitAction="saveCustomer"
    :isEditMode="$isEditMode"
    saveText="Save Customer"
    updateText="Update Customer">

    <!-- 1. Basic & Company Information -->
    <div class="form-section-title">1. Basic & Company Information</div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Customer Code <span class="text-danger">*</span></label>
            <input type="text" wire:model="customer_code" class="form-control @error('customer_code') is-invalid @enderror">
            @error('customer_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Company Name (If Business Client)</label>
            <input type="text" wire:model="company_name" class="form-control" placeholder="e.g. Modern Trading LLC">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Customer / Primary Contact Name <span class="text-danger">*</span></label>
            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Jane Smith">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">TRN / Tax Number</label>
            <input type="text" wire:model="trn_number" class="form-control" placeholder="15-digit TRN">
        </div>
    </div>

    <!-- 2. Contact Details -->
    <div class="form-section-title mt-2">2. Contact Details</div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Mobile Number</label>
            <input type="text" wire:model="mobile" class="form-control" placeholder="+971 50 987 6543">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" wire:model="email" class="form-control" placeholder="client@example.com">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Delivery / Billing Address</label>
        <textarea wire:model="address" class="form-control" rows="2" placeholder="Office / Warehouse address..."></textarea>
    </div>

    <!-- 3. Financial & Credit Terms -->
    <div class="form-section-title mt-2">3. Credit Limit & Terms</div>
    <div class="row">
        @if(!$isEditMode)
            <div class="col-md-4 mb-3">
                <label class="form-label">Opening Receivable (AED)</label>
                <input type="number" step="0.01" wire:model="opening_balance" class="form-control" placeholder="0.00">
            </div>
        @endif
        <div class="{{ $isEditMode ? 'col-md-6' : 'col-md-4' }} mb-3">
            <label class="form-label">Credit Limit (AED)</label>
            <input type="number" step="0.01" wire:model="credit_limit" class="form-control" placeholder="0.00">
        </div>
        <div class="{{ $isEditMode ? 'col-md-6' : 'col-md-4' }} mb-3">
            <label class="form-label">Payment Terms</label>
            <input type="text" wire:model="payment_terms" class="form-control" placeholder="e.g. Net 15 Days, Cash, Due on receipt">
        </div>
    </div>

    <div class="mb-2">
        <label class="form-label">Internal Notes</label>
        <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes regarding this customer..."></textarea>
    </div>
</x-modal>
