@if($selectedSale)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">Sales Invoice #{{ $selectedSale->invoice_number }}</h5>
                    <button type="button" class="btn-close" wire:click="closeDetails" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Customer:</strong> {{ $selectedSale->customer->name ?? 'Walk-in Customer' }}</p>
                            @if($selectedSale->customer && $selectedSale->customer->company_name)
                                <p class="mb-1 text-muted"><strong>Company:</strong> {{ $selectedSale->customer->company_name }}</p>
                            @endif
                            <p class="mb-1"><strong>Sale Date:</strong> {{ $selectedSale->sale_date }}</p>
                            <p class="mb-0"><strong>Payment Method:</strong> {{ $selectedSale->payment_type }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1">
                                <strong>Status:</strong>
                                <span class="badge {{ $selectedSale->status === 'Confirmed' ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                    {{ $selectedSale->status }}
                                </span>
                            </p>
                            <a href="{{ route('sales.pdf', ['id' => $selectedSale->id]) }}" target="_blank" class="btn btn-sm btn-danger mt-1">
                                <i class="bx bxs-file-pdf me-1"></i> Print / Download PDF Invoice
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Selling Price</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">VAT</th>
                                    <th class="text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedSale->items as $item)
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
                            <p class="font-size-13">{{ $selectedSale->notes ?? 'None' }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1 font-size-13">Subtotal: <strong>AED {{ number_format($selectedSale->subtotal, 2) }}</strong></p>
                            <p class="mb-1 font-size-13">VAT Amount: <strong>AED {{ number_format($selectedSale->vat_amount, 2) }}</strong></p>
                            @if($selectedSale->discount_amount > 0)
                                <p class="mb-1 font-size-13 text-danger">Discount: <strong>- AED {{ number_format($selectedSale->discount_amount, 2) }}</strong></p>
                            @endif
                            <h4 class="text-success fw-bold">Grand Total: AED {{ number_format($selectedSale->grand_total, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($selectedSale->status === 'Confirmed')
                        <button type="button" onclick="confirm('Cancel this sales invoice? Stock will be returned to inventory and transactions reversed.') || event.stopImmediatePropagation()" wire:click="cancelSale({{ $selectedSale->id }})" class="btn btn-danger me-auto">
                            <i class="bx bx-x-circle me-1"></i> Cancel Invoice (Reverse)
                        </button>
                    @endif
                    <button type="button" class="btn btn-light" wire:click="closeDetails">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
