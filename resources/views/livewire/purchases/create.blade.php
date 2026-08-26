<div>
    <!-- Page Header -->
    <x-page-header title="Create Purchase Invoice" subtitle="Record new vendor purchase bills, stock receiving, and payment terms.">
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Invoices
        </a>
    </x-page-header>

    <form wire:submit.prevent="savePurchase">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Invoice Header</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Purchase Number</label>
                        <input type="text" wire:model="purchase_number" class="form-control" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" wire:model="purchase_date" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Supplier</label>
                        <x-searchable-select wire:model="supplier_id" class="form-select" placeholder="Select Supplier...">
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }} @if($sup->company_name) ({{ $sup->company_name }}) @endif</option>
                            @endforeach
                        </x-searchable-select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Supplier Ref # / Bill No</label>
                        <input type="text" wire:model="reference_number" class="form-control" placeholder="Supplier invoice ref">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Terms / Type</label>
                        <x-searchable-select wire:model.live="payment_type" class="form-select">
                            <option value="Credit">Credit (Supplier Account Payable)</option>
                            <option value="Cash">Cash (Immediate Outflow)</option>
                            <option value="Bank">Bank Transfer / Card</option>
                        </x-searchable-select>
                    </div>
                    @if(in_array($payment_type, ['Cash', 'Bank']))
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Account</label>
                            <x-searchable-select wire:model="account_id" class="form-select">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} (AED {{ number_format($acc->current_balance, 2) }})</option>
                                @endforeach
                            </x-searchable-select>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="card border-0 shadow-sm mb-4" style="overflow: visible;">
            <div class="card-body" style="overflow: visible;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Purchase Items</h5>
                    <button type="button" wire:click="addItem" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-plus me-1"></i> Add Line Item
                    </button>
                </div>

                <div class="table-responsive" style="overflow: visible;">
                    <table class="table align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 35%;">Product</th>
                                <th style="width: 12%;">Qty</th>
                                <th style="width: 15%;">Unit Price (AED)</th>
                                <th style="width: 10%;">VAT %</th>
                                <th style="width: 15%;" class="text-end">Line Total</th>
                                <th style="width: 5%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                                <tr wire:key="item-row-{{ $index }}">
                                    <td>
                                        <x-searchable-select wire:model.live="items.{{ $index }}.product_id" class="form-select" placeholder="Select Product...">
                                            @foreach($products as $p)
                                                @php
                                                    $catName = $p->category ? $p->category->name : 'General';
                                                    $stock = (float)$p->current_stock;
                                                    $stockLabel = $stock > 0 ? "Stock: " . number_format($stock, $stock == (int)$stock ? 0 : 2) : "Out of Stock: 0";
                                                @endphp
                                                <option value="{{ $p->id }}">
                                                    {{ $p->name }} - {{ $catName }} [{{ $p->product_code }}] — {{ $stockLabel }}
                                                </option>
                                            @endforeach
                                        </x-searchable-select>
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
                        <label class="form-label">Notes / Remarks</label>
                        <textarea wire:model="notes" class="form-control" rows="3" placeholder="Purchase order details..."></textarea>
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
                                <span>Global Discount:</span>
                                <input type="number" step="0.01" wire:model.live.debounce.300ms="discount_amount" class="form-control form-control-sm w-50 text-end">
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between font-size-18">
                                <span class="fw-bold">Grand Total:</span>
                                <span class="fw-bold text-primary">AED {{ number_format($grand_total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bx bx-check-circle me-1"></i> Confirm & Save Purchase
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
