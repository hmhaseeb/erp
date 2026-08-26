<div>
    <!-- Page Header -->
    <x-page-header title="Create Sales Invoice" subtitle="Generate a new tax invoice for customer sales.">
        <a href="{{ route('sales.index') }}" class="btn btn-light">
            <i class="bx bx-arrow-back me-1"></i> Back to Invoices
        </a>
    </x-page-header>

    <form wire:submit.prevent="saveSale">
        <!-- Invoice Header Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0">Invoice Header & Customer Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" wire:model="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" readonly>
                        @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="sale_date" class="form-control @error('sale_date') is-invalid @enderror">
                        @error('sale_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                        <x-searchable-select wire:model="customer_id" class="form-select {{ $errors->has('customer_id') ? 'is-invalid' : '' }}" placeholder="Select Customer...">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company_name ?? 'Individual' }})</option>
                            @endforeach
                        </x-searchable-select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                        <x-searchable-select wire:model.live="payment_type" class="form-select">
                            <option value="Cash">Cash Sale</option>
                            <option value="Bank">Bank / Online</option>
                            <option value="Credit">Credit (Receivable)</option>
                        </x-searchable-select>
                    </div>
                </div>

                @if($payment_type !== 'Credit')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Deposit Account <span class="text-danger">*</span></label>
                            <x-searchable-select wire:model="account_id" class="form-select {{ $errors->has('account_id') ? 'is-invalid' : '' }}" placeholder="Select Account...">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }}) - AED {{ number_format($acc->current_balance, 2) }}</option>
                                @endforeach
                            </x-searchable-select>
                            @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="card border-0 shadow-sm mb-4" style="overflow: visible;">
            <div class="card-body" style="overflow: visible;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Invoice Line Items</h5>
                    <button type="button" wire:click="addItem" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-plus me-1"></i> Add Line Item
                    </button>
                </div>

                @if($products->isEmpty())
                    <div class="alert alert-warning font-size-13 py-2 px-3 mb-3" role="alert">
                        <i class="bx bx-error-circle me-1"></i> <strong>No in-stock products available:</strong> All products are currently out of stock (Stock: 0). Please record a purchase invoice or stock adjustment before selling.
                    </div>
                @endif

                <div class="table-responsive" style="overflow: visible;">
                    <table class="table align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">Product</th>
                                <th style="width: 12%;">Qty</th>
                                <th style="width: 15%;">Unit Selling Price (AED)</th>
                                <th style="width: 10%;">VAT %</th>
                                <th style="width: 15%;" class="text-end">Line Total</th>
                                <th style="width: 8%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                                <tr wire:key="sale-item-{{ $index }}">
                                    <td style="min-width: 280px; position: relative;">
                                        <x-searchable-select wire:model.live="items.{{ $index }}.product_id" class="form-select {{ $errors->has('items.'.$index.'.product_id') ? 'is-invalid' : '' }}" placeholder="Select In-Stock Product...">
                                            @foreach($products as $p)
                                                @php
                                                    $catName = $p->category ? $p->category->name : 'General';
                                                    $stock = (float)$p->current_stock;
                                                    $stockLabel = "Stock: " . number_format($stock, $stock == (int)$stock ? 0 : 2);
                                                @endphp
                                                <option value="{{ $p->id }}">
                                                    {{ $p->name }} - {{ $catName }} [{{ $p->product_code }}] — {{ $stockLabel }}
                                                </option>
                                            @endforeach
                                        </x-searchable-select>
                                        @if($errors->has('items.'.$index.'.product_id'))
                                            <div class="invalid-feedback d-block font-size-11">{{ $errors->first('items.'.$index.'.product_id') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $index }}.quantity" class="form-control {{ $errors->has('items.'.$index.'.quantity') ? 'is-invalid' : '' }}">
                                        @if($errors->has('items.'.$index.'.quantity'))
                                            <div class="invalid-feedback d-block font-size-11">{{ $errors->first('items.'.$index.'.quantity') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $index }}.unit_price" class="form-control">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $index }}.vat_percent" class="form-control">
                                    </td>
                                    <td class="text-end fw-bold font-monospace">
                                        AED {{ number_format($item['line_total'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-sm btn-outline-danger" title="Remove Item">
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
                                <span class="fw-bold font-monospace">AED {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Discount (AED):</span>
                                <input type="number" step="0.01" wire:model.live.debounce.300ms="discount_amount" class="form-control form-control-sm text-end font-monospace" style="width: 120px;" placeholder="0.00">
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>VAT Total:</span>
                                <span class="fw-bold font-monospace">AED {{ number_format($vat_amount, 2) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between font-size-16">
                                <span class="fw-bold">Grand Total:</span>
                                <span class="fw-bold text-primary font-monospace">AED {{ number_format($grand_total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                <a href="{{ route('sales.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-success px-4" {{ $products->isEmpty() ? 'disabled' : '' }}>
                    <span wire:loading.remove wire:target="saveSale">
                        <i class="bx bx-check me-1"></i> Generate & Issue Invoice
                    </span>
                    <span wire:loading wire:target="saveSale">
                        <i class="bx bx-loader-alt bx-spin me-1"></i> Processing...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
