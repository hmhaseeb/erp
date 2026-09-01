<x-modal 
    :isOpen="$isCustomerModalOpen" 
    title="Register New Customer"
    size="modal-lg modal-dialog-centered modal-dialog-scrollable"
    submitAction="saveNewCustomer"
    closeAction="closeCustomerModal"
    saveText="Save & Select Customer"
    savingText="Registering Customer..."
    theme="primary">

    <!-- 1. Basic & Company Information -->
    <div class="form-section-title">1. Basic & Company Information</div>
    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Customer Code <span class="text-danger">*</span></label>
            <input type="text" wire:model="cust_code" class="form-control @error('cust_code') is-invalid @enderror">
            @error('cust_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Company Name (If Business Client)</label>
            <input type="text" wire:model="cust_company_name" class="form-control" placeholder="e.g. Modern Trading LLC">
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Customer / Contact Person Name <span class="text-danger">*</span></label>
            <input type="text" wire:model="cust_name" class="form-control @error('cust_name') is-invalid @enderror" placeholder="e.g. Jane Smith">
            @error('cust_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">TRN / Tax Number</label>
            <input type="text" wire:model="cust_trn_number" class="form-control" placeholder="15-digit TRN">
        </div>
    </div>

    <!-- 2. Contact Details -->
    <div class="form-section-title mt-2">2. Contact Details</div>
    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Mobile Number</label>
            <input type="text" wire:model="cust_mobile" class="form-control" placeholder="+971 50 987 6543">
            @error('cust_mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label font-size-12">Email Address</label>
            <input type="email" wire:model="cust_email" class="form-control" placeholder="client@example.com">
            @error('cust_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label font-size-12">Delivery / Billing Address</label>
        <textarea wire:model="cust_address" class="form-control" rows="2" placeholder="Office / Warehouse address..."></textarea>
    </div>

    <!-- 3. Financial & Credit Terms -->
    <div class="form-section-title mt-2">3. Credit Limit & Terms</div>
    <div class="row">
        <div class="col-12 col-sm-4 mb-3">
            <label class="form-label font-size-12">Opening Receivable (AED)</label>
            <input type="number" step="0.01" wire:model="cust_opening_balance" class="form-control" placeholder="0.00">
            @error('cust_opening_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-4 mb-3">
            <label class="form-label font-size-12">Credit Limit (AED)</label>
            <input type="number" step="0.01" wire:model="cust_credit_limit" class="form-control" placeholder="0.00">
            @error('cust_credit_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-4 mb-3">
            <label class="form-label font-size-12">Payment Terms (Days)</label>
            <input type="text" wire:model="cust_payment_terms" class="form-control" placeholder="e.g. 30 Days Net">
        </div>
    </div>

    <div class="mb-2">
        <label class="form-label font-size-12">Internal Notes</label>
        <textarea wire:model="cust_notes" class="form-control" rows="2" placeholder="Private internal notes..."></textarea>
    </div>
</x-modal>
