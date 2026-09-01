@if($selectedSale)
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
                    <h5 class="modal-title font-size-16 fw-bold">Sales Invoice #{{ $selectedSale->invoice_number }}</h5>
                    <button type="button" class="btn-close" @click.stop="close()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 g-2">
                        <div class="col-12 col-md-7">
                            <p class="mb-1"><strong>Customer:</strong> {{ $selectedSale->customer->name ?? 'Walk-in Customer' }}</p>
                            @if($selectedSale->customer && $selectedSale->customer->company_name)
                                <p class="mb-1 text-muted"><strong>Company:</strong> {{ $selectedSale->customer->company_name }}</p>
                            @endif
                            <p class="mb-1"><strong>Sale Date:</strong> {{ $selectedSale->sale_date }}</p>
                            <p class="mb-0"><strong>Payment Method:</strong> {{ $selectedSale->payment_type }}</p>
                        </div>
                        <div class="col-12 col-md-5 text-start text-md-end mt-2 mt-md-0">
                            <div class="mb-2">
                                <span class="text-muted font-size-12 me-1">Status:</span>
                                <span class="badge {{ $selectedSale->status === 'Confirmed' ? 'badge-soft-success' : 'badge-soft-danger' }} font-size-12">
                                    {{ $selectedSale->status }}
                                </span>
                            </div>
                            <a href="{{ route('sales.pdf', ['id' => $selectedSale->id]) }}" target="_blank" class="btn btn-sm btn-danger w-100 w-md-auto">
                                <i class="bx bxs-file-pdf me-1"></i> Print / Download PDF
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm font-size-13 table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 140px;">Product</th>
                                    <th class="text-end text-nowrap" style="min-width: 60px;">Qty</th>
                                    <th class="text-end text-nowrap" style="min-width: 95px;">Selling Price</th>
                                    <th class="text-end text-nowrap" style="min-width: 80px;">Discount</th>
                                    <th class="text-end text-nowrap" style="min-width: 70px;">VAT</th>
                                    <th class="text-end text-nowrap" style="min-width: 95px;">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedSale->items as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $item->product->name ?? 'Product' }}</span>
                                            @if($item->product && $item->product->product_code)
                                                <small class="text-muted d-block font-size-11">{{ $item->product->product_code }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end font-monospace">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-end font-monospace">AED {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end font-monospace">AED {{ number_format($item->discount_amount, 2) }}</td>
                                        <td class="text-end font-monospace">AED {{ number_format($item->vat_amount, 2) }}</td>
                                        <td class="text-end fw-bold font-monospace">AED {{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block fw-semibold mb-1">Notes / Special Instructions:</small>
                            <div class="p-2 bg-light rounded font-size-13 text-muted">
                                {{ $selectedSale->notes ?: 'No special instructions recorded.' }}
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between mb-1 font-size-13">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="font-monospace fw-semibold">AED {{ number_format($selectedSale->subtotal, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 font-size-13">
                                    <span class="text-muted">VAT Amount:</span>
                                    <span class="font-monospace fw-semibold">AED {{ number_format($selectedSale->vat_amount, 2) }}</span>
                                </div>
                                @if($selectedSale->discount_amount > 0)
                                    <div class="d-flex justify-content-between mb-1 font-size-13 text-danger">
                                        <span>Discount:</span>
                                        <span class="font-monospace fw-semibold">- AED {{ number_format($selectedSale->discount_amount, 2) }}</span>
                                    </div>
                                @endif
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Grand Total:</span>
                                    <span class="text-success fw-bold font-monospace fs-5">AED {{ number_format($selectedSale->grand_total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column-reverse flex-sm-row justify-content-sm-between gap-2 align-items-stretch align-items-sm-center">
                    @if($selectedSale->status === 'Confirmed')
                        <button type="button" onclick="confirm('Cancel this sales invoice? Stock will be returned to inventory and transactions reversed.') || event.stopImmediatePropagation()" wire:click="cancelSale({{ $selectedSale->id }})" class="btn btn-outline-danger w-100 w-sm-auto me-sm-auto">
                            <i class="bx bx-x-circle me-1"></i> Cancel Invoice (Reverse)
                        </button>
                    @endif
                    <button type="button" class="btn btn-light w-100 w-sm-auto ms-sm-auto" @click.stop="close()">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
