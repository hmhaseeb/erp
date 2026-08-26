<x-modal 
    :isOpen="$isModalOpen" 
    title="Record Operating Expense"
    size="modal-lg modal-dialog-centered"
    submitAction="saveExpense"
    saveText="Save Expense"
    updateText="Save Expense"
    theme="danger">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Expense Date <span class="text-danger">*</span></label>
            <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Expense Category <span class="text-danger">*</span></label>
            <x-searchable-select wire:model="expense_category_id" class="form-select @error('expense_category_id') is-invalid @enderror" placeholder="Select Category...">
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </x-searchable-select>
            @error('expense_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Disbursed From Account <span class="text-danger">*</span></label>
            <x-searchable-select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror" placeholder="Select Account...">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: AED {{ number_format($acc->current_balance, 2) }})</option>
                @endforeach
            </x-searchable-select>
            @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Amount (AED) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Description / Particulars</label>
        <input type="text" wire:model="description" class="form-control" placeholder="e.g. Office internet monthly subscription, office tea/coffee">
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Bill / Voucher Reference</label>
            <input type="text" wire:model="reference_number" class="form-control" placeholder="Invoice # or voucher ref">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Upload Receipt / Bill (Optional)</label>
            <input type="file" wire:model="attachment" class="form-control @error('attachment') is-invalid @enderror">
            @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-2">
        <label class="form-label">Internal Notes</label>
        <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
    </div>
</x-modal>
