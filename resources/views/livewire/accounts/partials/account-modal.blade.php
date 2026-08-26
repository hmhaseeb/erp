<x-modal 
    :isOpen="$isModalOpen" 
    :title="$isEditMode ? 'Edit Financial Account' : 'Create Cash / Bank Account'"
    submitAction="saveAccount"
    :isEditMode="$isEditMode"
    saveText="Save Account"
    updateText="Update Account">

    <div class="mb-3">
        <label class="form-label">Account Title <span class="text-danger">*</span></label>
        <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Main Cash Drawer, Emirates NBD Operating">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Account Type <span class="text-danger">*</span></label>
        <x-searchable-select wire:model.live="type" class="form-select @error('type') is-invalid @enderror">
            <option value="Cash">Cash Account</option>
            <option value="Bank">Bank Account</option>
            <option value="Other">Other</option>
        </x-searchable-select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if($type === 'Bank')
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Bank Name</label>
                <input type="text" wire:model="bank_name" class="form-control" placeholder="e.g. Emirates NBD, ADCB">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Account / IBAN Number</label>
                <input type="text" wire:model="account_number" class="form-control" placeholder="Account # or IBAN">
            </div>
        </div>
    @endif

    @if(!$isEditMode)
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Opening Balance (AED)</label>
                <input type="number" step="0.01" wire:model="opening_balance" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Opening Balance Date <span class="text-danger">*</span></label>
                <input type="date" wire:model="opening_balance_date" class="form-control">
            </div>
        </div>
    @endif

    <div class="mb-2">
        <label class="form-label">Description / Notes</label>
        <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
    </div>
</x-modal>
