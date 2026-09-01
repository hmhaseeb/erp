<x-modal 
    :isOpen="$isModalOpen" 
    title="Record Customer Payment Receipt"
    size="modal-lg modal-dialog-centered"
    submitAction="savePayment"
    saveText="Save Receipt"
    updateText="Save Receipt"
    savingText="Recording..."
    theme="success">

    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" wire:model="payment_date" class="form-control @error('payment_date') is-invalid @enderror">
            @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Select Customer <span class="text-danger">*</span></label>
            <x-searchable-select wire:model.live="customer_id" class="form-select @error('customer_id') is-invalid @enderror" placeholder="Select Customer...">
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} (Receivable: AED {{ number_format($c->current_balance, 2) }})</option>
                @endforeach
            </x-searchable-select>
            @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Deposit Into Account <span class="text-danger">*</span></label>
            <x-searchable-select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror" placeholder="Select Account...">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: AED {{ number_format($acc->current_balance, 2) }})</option>
                @endforeach
            </x-searchable-select>
            @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Total Amount Received (AED) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Cheque / Reference Number</label>
            <input type="text" wire:model="reference_number" class="form-control" placeholder="Cheque # or transaction reference">
        </div>
        <div class="col-12 col-sm-6 mb-3">
            <label class="form-label">Notes / Remarks</label>
            <input type="text" wire:model="notes" class="form-control" placeholder="Payment remarks...">
        </div>
    </div>

    @if(count($unpaidSales) > 0)
        <div class="mt-2">
            <label class="form-label fw-bold">Allocate to Unpaid Invoices (Optional):</label>
            <div class="table-responsive" style="max-height: 200px;">
                <table class="table table-sm table-bordered font-size-12 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th class="text-end">Due Amount</th>
                            <th style="width: 130px;" class="text-end">Allocate (AED)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unpaidSales as $sale)
                            <tr>
                                <td><code>{{ $sale->invoice_number }}</code></td>
                                <td>{{ $sale->sale_date }}</td>
                                <td class="text-end font-monospace text-danger">AED {{ number_format($sale->due_amount, 2) }}</td>
                                <td>
                                    <input type="number" step="0.01" wire:model="allocations.{{ $sale->id }}" class="form-control form-control-sm text-end" placeholder="0.00">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-modal>
