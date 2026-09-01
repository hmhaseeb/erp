@if($selectedPurchase)
    <div x-data="{
            closing: false,
            close() {
                if (this.closing) return;
                this.closing = true;
                $wire.closeDetails();
            }
         }"
         @keydown.escape.window.stop="close()"
         @click.self.stop="close()"
         class="modal fade show d-block erp-modal-backdrop" 
         style="background: rgba(0,0,0,0.5); z-index: 1055;" 
         tabindex="-1" 
         role="dialog" 
         aria-modal="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable erp-modal-dialog" @click.stop>
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title font-size-16 fw-bold">Purchase Invoice #{{ $selectedPurchase->purchase_number }}</h5>
                    <button type="button" class="btn-close" @click.stop="close()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 g-2">
                        <div class="col-12 col-md-6">
                            <p class="mb-1"><strong>Supplier:</strong> {{ $selectedPurchase->supplier->name ?? '-' }}</p>
                            @if($selectedPurchase->supplier && $selectedPurchase->supplier->company_name)
                                <p class="mb-1 text-muted"><strong>Company:</strong> {{ $selectedPurchase->supplier->company_name }}</p>
                            @endif
                            <p class="mb-1"><strong>Date:</strong> {{ $selectedPurchase->purchase_date }}</p>
                            <p class="mb-0"><strong>Payment Method:</strong> {{ $selectedPurchase->payment_type }}</p>
                        </div>
                        <div class="col-12 col-md-6 text-start text-md-end">
                            <p class="mb-1">
                                <strong>Status:</strong>
                                <span class="badge {{ $selectedPurchase->status === 'Confirmed' ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                    {{ $selectedPurchase->status }}
                                </span>
                            </p>
                            @if($selectedPurchase->reference_number)
                                <p class="mb-1 text-muted"><strong>Supplier Bill Ref:</strong> {{ $selectedPurchase->reference_number }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm font-size-13 align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap" style="min-width: 140px;">Product</th>
                                    <th class="text-end text-nowrap">Qty</th>
                                    <th class="text-end text-nowrap">Unit Cost</th>
                                    <th class="text-end text-nowrap">Discount</th>
                                    <th class="text-end text-nowrap">VAT</th>
                                    <th class="text-end text-nowrap">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedPurchase->items as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $item->product->name ?? 'Product' }}</span>
                                            <small class="text-muted d-block font-size-11">{{ $item->product->product_code ?? '' }}</small>
                                        </td>
                                        <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-end">AED {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">AED {{ number_format($item->discount_amount, 2) }}</td>
                                        <td class="text-end">AED {{ number_format($item->vat_amount, 2) }}</td>
                                        <td class="text-end fw-bold text-dark">AED {{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Notes / Remarks:</small>
                            <p class="font-size-13 text-muted mb-2">{{ $selectedPurchase->notes ?? 'No notes recorded.' }}</p>
                        </div>
                        <div class="col-12 col-md-6 text-start text-md-end">
                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between mb-1 font-size-13">
                                    <span class="text-muted">Subtotal:</span>
                                    <strong>AED {{ number_format($selectedPurchase->subtotal, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1 font-size-13">
                                    <span class="text-muted">VAT Amount:</span>
                                    <strong>AED {{ number_format($selectedPurchase->vat_amount, 2) }}</strong>
                                </div>
                                @if($selectedPurchase->discount_amount > 0)
                                    <div class="d-flex justify-content-between mb-1 font-size-13 text-danger">
                                        <span>Discount:</span>
                                        <strong>- AED {{ number_format($selectedPurchase->discount_amount, 2) }}</strong>
                                    </div>
                                @endif
                                <div class="border-top pt-2 mt-1 d-flex justify-content-between align-items-center">
                                    <span class="fw-bold font-size-14 text-dark">Grand Total:</span>
                                    <span class="text-primary fw-bold font-size-16">AED {{ number_format($selectedPurchase->grand_total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column-reverse flex-sm-row justify-content-sm-between gap-2 align-items-stretch align-items-sm-center">
                    @if($selectedPurchase->status === 'Confirmed')
                        <button type="button" onclick="confirm('Are you sure you want to cancel this purchase invoice? Stock will be removed from inventory and accounting entries reversed.') || event.stopImmediatePropagation()" wire:click="cancelPurchase({{ $selectedPurchase->id }})" class="btn btn-outline-danger w-100 w-sm-auto">
                            <i class="bx bx-x-circle me-1"></i> Cancel Purchase (Reverse)
                        </button>
                    @else
                        <div></div>
                    @endif
                    <button type="button" class="btn btn-light w-100 w-sm-auto ms-sm-auto" @click.stop="close()">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
