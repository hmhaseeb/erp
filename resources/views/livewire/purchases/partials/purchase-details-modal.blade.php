@if($selectedPurchase)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">Purchase Invoice #{{ $selectedPurchase->purchase_number }}</h5>
                    <button type="button" class="btn-close" wire:click="closeDetails" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Supplier:</strong> {{ $selectedPurchase->supplier->name ?? '-' }}</p>
                            @if($selectedPurchase->supplier && $selectedPurchase->supplier->company_name)
                                <p class="mb-1 text-muted"><strong>Company:</strong> {{ $selectedPurchase->supplier->company_name }}</p>
                            @endif
                            <p class="mb-1"><strong>Date:</strong> {{ $selectedPurchase->purchase_date }}</p>
                            <p class="mb-0"><strong>Payment Method:</strong> {{ $selectedPurchase->payment_type }}</p>
                        </div>
                        <div class="col-md-6 text-end">
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
                        <table class="table table-bordered table-sm font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">VAT</th>
                                    <th class="text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedPurchase->items as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $item->product->name ?? 'Product' }}</span>
                                            <small class="text-muted d-block">{{ $item->product->product_code ?? '' }}</small>
                                        </td>
                                        <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-end">AED {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">AED {{ number_format($item->discount_amount, 2) }}</td>
                                        <td class="text-end">AED {{ number_format($item->vat_amount, 2) }}</td>
                                        <td class="text-end fw-bold">AED {{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Notes:</small>
                            <p class="font-size-13">{{ $selectedPurchase->notes ?? 'None' }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1 font-size-13">Subtotal: <strong>AED {{ number_format($selectedPurchase->subtotal, 2) }}</strong></p>
                            <p class="mb-1 font-size-13">VAT Amount: <strong>AED {{ number_format($selectedPurchase->vat_amount, 2) }}</strong></p>
                            @if($selectedPurchase->discount_amount > 0)
                                <p class="mb-1 font-size-13 text-danger">Discount: <strong>- AED {{ number_format($selectedPurchase->discount_amount, 2) }}</strong></p>
                            @endif
                            <h4 class="text-primary fw-bold">Grand Total: AED {{ number_format($selectedPurchase->grand_total, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($selectedPurchase->status === 'Confirmed')
                        <button type="button" onclick="confirm('Are you sure you want to cancel this purchase invoice? Stock will be removed from inventory and accounting entries reversed.') || event.stopImmediatePropagation()" wire:click="cancelPurchase({{ $selectedPurchase->id }})" class="btn btn-danger me-auto">
                            <i class="bx bx-x-circle me-1"></i> Cancel Purchase (Reverse)
                        </button>
                    @endif
                    <button type="button" class="btn btn-light" wire:click="closeDetails">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
