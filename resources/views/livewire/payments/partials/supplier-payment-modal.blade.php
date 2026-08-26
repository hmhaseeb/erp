<x-modal 
    :isOpen="$isModalOpen" 
    title="Record Supplier Payment Voucher"
    size="modal-lg modal-dialog-centered"
    submitAction="savePayment"
    saveText="Save Payment Voucher"
    updateText="Save Payment Voucher"
    savingText="Recording..."
    theme="primary">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" wire:model="payment_date" class="form-control @error('payment_date') is-invalid @enderror">
            @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Select Supplier <span class="text-danger">*</span></label>
            <x-searchable-select wire:model.live="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" placeholder="Select Supplier...">
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} (Payable: AED {{ number_format($s->current_balance, 2) }})</option>
                @endforeach
            </x-searchable-select>
            @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Disburse From Account <span class="text-danger">*</span></label>
            <x-searchable-select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror" placeholder="Select Account...">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: AED {{ number_format($acc->current_balance, 2) }})</option>
                @endforeach
            </x-searchable-select>
            @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Total Amount Paid (AED) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Cheque / Transfer Reference #</label>
            <input type="text" wire:model="reference_number" class="form-control" placeholder="Cheque # or bank ref">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Notes / Remarks</label>
            <input type="text" wire:model="notes" class="form-control" placeholder="Payment voucher remarks...">
        </div>
    </div>

    @if(count($unpaidPurchases) > 0)
        <div class="mt-2">
            <label class="form-label fw-bold">Allocate to Unpaid Purchase Invoices (Optional):</label>
            <div class="table-responsive" style="max-height: 200px;">
                <table class="table table-sm table-bordered font-size-12 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Purchase #</th>
                            <th>Date</th>
                            <th class="text-end">Due Amount</th>
                            <th style="width: 130px;" class="text-end">Allocate (AED)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unpaidPurchases as $pur)
                            <tr>
                                <td><code>{{ $pur->purchase_number }}</code></td>
                                <td>{{ $pur->purchase_date }}</td>
                                <td class="text-end font-monospace text-danger">AED {{ number_format($pur->due_amount, 2) }}</td>
                                <td>
                                    <input type="number" step="0.01" wire:model="allocations.{{ $pur->id }}" class="form-control form-control-sm text-end" placeholder="0.00">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-modal>
