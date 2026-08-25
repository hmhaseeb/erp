<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Create Sales Invoice</h4>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Invoices
                </a>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="saveSale">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Invoice Details</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" wire:model="invoice_number" class="form-control" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sale Date</label>
                        <input type="date" wire:model="sale_date" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Customer</label>
                        <select wire:model="customer_id" class="form-select">
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}">{{ $cust->name }} @if($cust->company_name) ({{ $cust->company_name }}) @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Terms / Type</label>
                        <select wire:model.live="payment_type" class="form-select">
                            <option value="Cash">Cash (Immediate Receipt)</option>
                            <option value="Bank">Bank Deposit / Card</option>
                            <option value="Credit">Credit (Customer Account Receivable)</option>
                        </select>
                    </div>
                    @if(in_array($payment_type, ['Cash', 'Bank']))
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Receiving Account</label>
                            <select wire:model="account_id" class="form-select">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} (AED {{ number_format($acc->current_balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Invoice Line Items</h5>
                    <button type="button" wire:click="addItem" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-plus me-1"></i> Add Line Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 35%;">Product</th>
                                <th style="width: 12%;">Qty</th>
                                <th style="width: 15%;">Unit Selling Price (AED)</th>
                                <th style="width: 10%;">VAT %</th>
                                <th style="width: 15%;" class="text-end">Line Total</th>
                                <th style="width: 5%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                                <tr>
                                    <td>
                                        <select wire:model.live="items.{{ $index }}.product_id" class="form-select">
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} (Stock: {{ number_format($p->current_stock, 2) }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $index }}.quantity" class="form-control">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $index }}.unit_price" class="form-control">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $index }}.vat_percent" class="form-control">
                                    </td>
                                    <td class="text-end fw-bold">
                                        AED {{ number_format($item['line_total'], 2) }}
                                    </td>
                                    <td>
                                        <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-sm btn-outline-danger">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <label class="form-label">Invoice Notes / Special Instructions</label>
                        <textarea wire:model="notes" class="form-control" rows="3" placeholder="Terms & conditions or delivery note..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span class="fw-bold">AED {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>VAT Total:</span>
                                <span class="fw-bold text-info">AED {{ number_format($vat_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 align-items-center">
                                <span>Discount Amount:</span>
                                <input type="number" step="0.01" wire:model.live.debounce.300ms="discount_amount" class="form-control form-control-sm w-50 text-end">
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between font-size-18">
                                <span class="fw-bold">Grand Total:</span>
                                <span class="fw-bold text-success">AED {{ number_format($grand_total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="bx bx-check-circle me-1"></i> Confirm & Issue Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
