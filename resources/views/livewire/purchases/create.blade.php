<div>
    <!-- Page Header -->
    <x-page-header title="Create Purchase Invoice" subtitle="Record new vendor purchase bills, stock receiving, and payment terms.">
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-arrow-back me-1"></i> Back to Invoices
        </a>
    </x-page-header>

    <form wire:submit.prevent="savePurchase">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-size-15 fw-semibold text-dark">Invoice Header & Vendor Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label font-size-13 fw-semibold">Purchase Number <span class="text-danger">*</span></label>
                        <input type="text" wire:model="purchase_number" class="form-control font-monospace bg-light" readonly>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label font-size-13 fw-semibold">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="purchase_date" class="form-control">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label font-size-13 fw-semibold mb-0">Supplier <span class="text-danger">*</span></label>
                            <button type="button" wire:click="openSupplierModal" class="btn btn-link btn-sm p-0 text-primary fw-semibold font-size-12 text-decoration-none" title="Register New Supplier">
                                <i class="bx bx-user-plus me-1"></i>Add New
                            </button>
                        </div>
                        <x-searchable-select wire:model.live="supplier_id" class="form-select {{ $errors->has('supplier_id') ? 'is-invalid' : '' }}" placeholder="Select Supplier...">
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }} @if($sup->company_name) ({{ $sup->company_name }}) @endif</option>
                            @endforeach
                        </x-searchable-select>
                        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label font-size-13 fw-semibold">Supplier Ref # / Bill No</label>
                        <input type="text" wire:model="reference_number" class="form-control" placeholder="Vendor invoice reference">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label font-size-13 fw-semibold">Payment Type <span class="text-danger">*</span></label>
                        <x-searchable-select wire:model.live="payment_type" class="form-select">
                            <option value="Credit">Credit (Supplier Account Payable)</option>
                            <option value="Cash">Cash (Immediate Outflow)</option>
                            <option value="Bank">Bank Transfer / Card</option>
                        </x-searchable-select>
                    </div>
                    @if(in_array($payment_type, ['Cash', 'Bank']))
                        <div class="col-12 col-sm-6 col-lg-6">
                            <label class="form-label font-size-13 fw-semibold">Deposit / Payment Account <span class="text-danger">*</span></label>
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
                    <h5 class="card-title mb-0 font-size-15 fw-semibold text-dark">Purchase Line Items</h5>
                    <span class="badge bg-light text-muted border font-size-12">{{ count($items) }} {{ count($items) === 1 ? 'Item' : 'Items' }}</span>
                </div>

                <div class="table-responsive purchases-table-container" style="overflow: visible;">
                    <table class="table align-middle mb-0 font-size-13 purchases-items-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 38%; min-width: 260px;">Product</th>
                                <th style="width: 12%; min-width: 90px;" class="text-center">Qty</th>
                                <th style="width: 16%; min-width: 130px;" class="text-end">Unit Cost (AED)</th>
                                <th style="width: 10%; min-width: 90px;" class="text-center">VAT %</th>
                                <th style="width: 16%; min-width: 120px;" class="text-end">Line Total</th>
                                <th style="width: 8%; min-width: 60px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                                <tr wire:key="purchase-item-{{ $index }}" class="purchase-item-row">
                                    <td class="col-product" style="position: relative;">
                                        <div class="d-flex justify-content-between align-items-center mb-1 d-md-none">
                                            <span class="badge bg-primary-subtle text-primary font-size-11 fw-semibold">Item #{{ $index + 1 }}</span>
                                            @if(count($items) > 1)
                                                <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-sm btn-outline-danger p-1 line-item-mobile-del" title="Remove Item">
                                                    <i class="bx bx-trash font-size-14"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @php
                                            $otherSelectedProductIds = [];
                                            foreach ($items as $otherIdx => $otherItem) {
                                                if ($otherIdx !== $index && !empty($otherItem['product_id'])) {
                                                    $otherSelectedProductIds[] = (string)$otherItem['product_id'];
                                                }
                                            }
                                        @endphp
                                        <label class="form-label font-size-11 text-muted d-md-none mb-1">Product <span class="text-danger">*</span></label>
                                        <x-searchable-select wire:model.live="items.{{ $index }}.product_id" class="form-select {{ $errors->has('items.'.$index.'.product_id') ? 'is-invalid' : '' }}" placeholder="Select Product...">
                                            @foreach($products as $p)
                                                @if(!in_array((string)$p->id, $otherSelectedProductIds, true))
                                                    @php
                                                        $catName = $p->category ? $p->category->name : 'General';
                                                        $stock = (float)$p->current_stock;
                                                        $stockLabel = $stock > 0 ? "Stock: " . number_format($stock, $stock == (int)$stock ? 0 : 2) : "Out of Stock: 0";
                                                    @endphp
                                                    <option value="{{ $p->id }}">
                                                        {{ $p->name }} - {{ $catName }} [{{ $p->product_code }}] — {{ $stockLabel }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </x-searchable-select>
                                        @if($errors->has('items.'.$index.'.product_id'))
                                            <div class="invalid-feedback d-block font-size-11">{{ $errors->first('items.'.$index.'.product_id') }}</div>
                                        @endif
                                    </td>
                                    <td class="col-qty">
                                        <label class="form-label font-size-11 text-muted d-md-none mb-1">Qty <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $index }}.quantity" class="form-control text-center {{ $errors->has('items.'.$index.'.quantity') ? 'is-invalid' : '' }}" placeholder="Qty">
                                        @if($errors->has('items.'.$index.'.quantity'))
                                            <div class="invalid-feedback d-block font-size-11">{{ $errors->first('items.'.$index.'.quantity') }}</div>
                                        @endif
                                    </td>
                                    <td class="col-price">
                                        <label class="form-label font-size-11 text-muted d-md-none mb-1">Cost (AED) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $index }}.unit_price" class="form-control text-end font-monospace" placeholder="Unit Cost">
                                    </td>
                                    <td class="col-vat">
                                        <label class="form-label font-size-11 text-muted d-md-none mb-1">VAT %</label>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $index }}.vat_percent" class="form-control text-center font-monospace" placeholder="VAT %">
                                    </td>
                                    <td class="col-total text-md-end font-monospace">
                                        <span class="d-md-none text-muted font-size-11 fw-normal me-2">Line Total:</span>
                                        <div class="d-flex align-items-center justify-content-end font-monospace fw-bold text-dark font-size-14" style="min-height: 38px;">
                                            AED {{ number_format($item['line_total'], 2) }}
                                        </div>
                                    </td>
                                    <td class="col-action text-center d-none d-md-table-cell">
                                        <div class="d-flex align-items-center justify-content-center" style="min-height: 38px;">
                                            @if(count($items) > 1)
                                                <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Remove Item">
                                                    <i class="bx bx-trash font-size-16"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-secondary p-0 d-inline-flex align-items-center justify-content-center disabled" disabled style="width: 32px; height: 32px; opacity: 0.35; cursor: not-allowed;" title="At least one item is required">
                                                    <i class="bx bx-trash font-size-16"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Action Buttons (Positioned Clearly Below the Table) -->
                <div class="mt-3 pt-1 d-flex flex-wrap gap-2">
                    <button type="button" wire:click="addItem" class="btn btn-sm btn-outline-primary px-3 py-2 fw-medium d-inline-flex align-items-center gap-1 shadow-sm">
                        <i class="bx bx-plus font-size-16"></i> Add Line Item
                    </button>
                    <button type="button" wire:click="openProductModal" class="btn btn-sm btn-outline-success px-3 py-2 fw-medium d-inline-flex align-items-center gap-1 shadow-sm">
                        <i class="bx bx-package font-size-16"></i> Add New Product
                    </button>
                </div>

                <!-- Notes and Totals -->
                <div class="row mt-4 g-3">
                    <div class="col-12 col-lg-6">
                        <label class="form-label font-size-13 fw-semibold">Invoice Notes / Vendor Memo</label>
                        <textarea wire:model="notes" class="form-control" rows="4" placeholder="Delivery notes, PO reference, terms..."></textarea>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="bg-light p-3 rounded">
                            <div class="d-flex justify-content-between mb-2 font-size-13">
                                <span class="text-muted">Subtotal:</span>
                                <span class="fw-bold font-monospace">AED {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 font-size-13">
                                <span class="text-muted">VAT Total:</span>
                                <span class="fw-bold font-monospace text-info">AED {{ number_format($vat_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 align-items-center font-size-13">
                                <span class="text-muted">Discount (AED):</span>
                                <input type="number" step="0.01" wire:model.live.debounce.300ms="discount_amount" class="form-control form-control-sm text-end font-monospace" style="max-width: 140px;" placeholder="0.00">
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold font-size-15 text-dark">Grand Total:</span>
                                <span class="fw-bold text-primary font-size-18 font-monospace">AED {{ number_format($grand_total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-between gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('purchases.index') }}" class="btn btn-light w-100 w-sm-auto">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                        <span wire:loading.remove wire:target="savePurchase">
                            <i class="bx bx-check-circle me-1"></i> Confirm & Save Purchase
                        </span>
                        <span wire:loading wire:target="savePurchase">
                            <i class="bx bx-loader-alt bx-spin me-1"></i> Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Quick Supplier Registration Modal (Lazy Loaded) -->
    @if($isSupplierModalOpen)
        @include('livewire.purchases.partials.quick-supplier-modal')
    @endif

    <!-- Quick Product Registration Modal (Reuses standard Product Create modal & form) -->
    @if($isProductModalOpen)
        @include('livewire.products.partials.product-modal', [
            'isOpen' => $isProductModalOpen,
            'title' => 'Register New Product',
            'submitAction' => 'saveNewProduct',
            'closeAction' => 'closeProductModal',
            'isEditMode' => false,
            'saveText' => 'Save & Select Product'
        ])
    @endif

    <style>
        /* Responsive Line Items Table for Mobile Devices */
        @media (max-width: 767.98px) {
            .purchases-items-table thead {
                display: none !important;
            }
            .purchases-items-table,
            .purchases-items-table tbody {
                display: block !important;
                width: 100% !important;
            }
            .purchases-items-table tr.purchase-item-row {
                display: flex !important;
                flex-wrap: wrap !important;
                background: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 8px !important;
                padding: 12px !important;
                margin-bottom: 12px !important;
            }
            .purchases-items-table td.col-product {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                padding: 0 0 8px 0 !important;
                border: none !important;
            }
            .purchases-items-table td.col-qty {
                flex: 0 0 28% !important;
                max-width: 28% !important;
                padding: 0 3px 8px 0 !important;
                border: none !important;
            }
            .purchases-items-table td.col-price {
                flex: 0 0 42% !important;
                max-width: 42% !important;
                padding: 0 3px 8px 3px !important;
                border: none !important;
            }
            .purchases-items-table td.col-vat {
                flex: 0 0 30% !important;
                max-width: 30% !important;
                padding: 0 0 8px 3px !important;
                border: none !important;
            }
            .purchases-items-table input.form-control {
                padding: 0.35rem 0.4rem !important;
                font-size: 13px !important;
            }
            .purchases-items-table td.col-total {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                padding: 8px 0 0 0 !important;
                border-top: 1px dashed #cbd5e1 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            .line-item-mobile-del {
                width: 28px;
                height: 28px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
            }
        }
        @media (min-width: 768px) {
            .purchases-items-table thead {
                display: table-header-group !important;
            }
            .purchases-items-table,
            .purchases-items-table tbody {
                display: table-row-group !important;
            }
            .purchases-items-table tr.purchase-item-row {
                display: table-row !important;
            }
            .purchases-items-table td.col-product {
                min-width: 260px;
                display: table-cell !important;
            }
            .purchases-items-table td.col-qty,
            .purchases-items-table td.col-price,
            .purchases-items-table td.col-vat,
            .purchases-items-table td.col-total,
            .purchases-items-table td.col-action {
                display: table-cell !important;
            }
        }
    </style>
</div>
