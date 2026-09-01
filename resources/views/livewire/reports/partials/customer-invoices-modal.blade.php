@if($selectedCustomer)
    <div x-data="{
            closing: false,
            close() {
                if (this.closing) return;
                this.closing = true;
                $wire.closeInvoicesModal();
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
                    <h5 class="modal-title font-size-16 fw-bold">Unpaid Invoices — {{ $selectedCustomer->name }}</h5>
                    <button type="button" class="btn-close" @click.stop="close()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-2 mb-3">
                        <span class="text-muted">Total Customer Balance: <strong class="text-danger font-monospace">AED {{ number_format($selectedCustomer->current_balance, 2) }}</strong></span>
                        <a href="{{ route('payments.customer') }}" class="btn btn-sm btn-success w-100 w-sm-auto">
                            <i class="bx bx-plus me-1"></i> Record Receipt Voucher
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm font-size-13 mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">Invoice #</th>
                                    <th class="text-nowrap">Date</th>
                                    <th class="text-nowrap">Grand Total</th>
                                    <th class="text-nowrap">Paid Amount</th>
                                    <th class="text-end text-danger text-nowrap">Due Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customerInvoices as $inv)
                                    <tr>
                                        <td><code>{{ $inv->invoice_number }}</code></td>
                                        <td>{{ $inv->sale_date }}</td>
                                        <td>AED {{ number_format($inv->grand_total, 2) }}</td>
                                        <td class="text-success">AED {{ number_format($inv->paid_amount, 2) }}</td>
                                        <td class="text-end font-monospace text-danger fw-bold">AED {{ number_format($inv->due_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No individual unpaid invoices found (balance may stem from opening balance).</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-sm-end">
                    <button type="button" class="btn btn-light w-100 w-sm-auto" @click.stop="close()">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
