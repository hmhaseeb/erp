<x-modal 
    :isOpen="$isModalOpen" 
    title="Record Account Transaction / Fund Transfer"
    submitAction="saveTransaction"
    saveText="Post Transaction"
    updateText="Post Transaction">

    <div class="mb-3">
        <label class="form-label">Transaction Date <span class="text-danger">*</span></label>
        <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
        <x-searchable-select wire:model.live="type" class="form-select @error('type') is-invalid @enderror">
            <option value="Cash In">Cash In (Deposit / Receipt)</option>
            <option value="Cash Out">Cash Out (Withdrawal / Payment)</option>
            <option value="Bank Deposit">Bank Deposit</option>
            <option value="Bank Withdrawal">Bank Withdrawal</option>
            <option value="Transfer">Account to Account Transfer</option>
        </x-searchable-select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">{{ $type === 'Transfer' ? 'From Source Account' : 'Account' }} <span class="text-danger">*</span></label>
        <x-searchable-select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror">
            @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: AED {{ number_format($acc->current_balance, 2) }})</option>
            @endforeach
        </x-searchable-select>
        @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if($type === 'Transfer')
        <div class="mb-3">
            <label class="form-label">To Destination Account <span class="text-danger">*</span></label>
            <x-searchable-select wire:model="to_account_id" class="form-select @error('to_account_id') is-invalid @enderror" placeholder="Select Target Account">
                <option value="">Select Target Account</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: AED {{ number_format($acc->current_balance, 2) }})</option>
                @endforeach
            </x-searchable-select>
            @error('to_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @endif

    <div class="mb-3">
        <label class="form-label">Amount (AED) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-2">
        <label class="form-label">Description / Remarks</label>
        <textarea wire:model="description" class="form-control" rows="2" placeholder="e.g. Bank cash deposit from daily register, fund transfer"></textarea>
    </div>
</x-modal>
