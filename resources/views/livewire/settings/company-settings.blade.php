<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Company Settings</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form wire:submit.prevent="saveSettings">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Trade Name</label>
                        <input type="text" wire:model="company_name" class="form-control">
                        @error('company_name') <span class="text-danger font-size-12">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Legal Entity Name</label>
                        <input type="text" wire:model="legal_name" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">TRN / VAT Tax Number</label>
                        <input type="text" wire:model="trn_number" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Currency Code</label>
                        <input type="text" wire:model="currency" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default VAT %</label>
                        <input type="number" step="0.01" wire:model="default_vat_percent" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Office Phone</label>
                        <input type="text" wire:model="phone" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" wire:model="mobile" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Email</label>
                        <input type="email" wire:model="email" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Website</label>
                        <input type="text" wire:model="website" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" wire:model="city" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" wire:model="country" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Address</label>
                    <textarea wire:model="address" class="form-control" rows="2"></textarea>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bx bx-save me-1"></i> Save Company Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
