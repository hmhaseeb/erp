@if($selectedSupplier)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">Unpaid Purchase Bills — {{ $selectedSupplier->name }}</h5>
                    <button type="button" class="btn-close" wire:click="closePurchasesModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Total Supplier Payable: <strong class="text-danger">AED {{ number_format($selectedSupplier->current_balance, 2) }}</strong></span>
                        <a href="{{ route('payments.supplier') }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-plus me-1"></i> Record Payment Voucher
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm font-size-13 mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Purchase #</th>
                                    <th>Date</th>
                                    <th>Grand Total</th>
                                    <th>Paid Amount</th>
                                    <th class="text-end text-danger">Due Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplierPurchases as $pur)
                                    <tr>
                                        <td><code>{{ $pur->purchase_number }}</code></td>
                                        <td>{{ $pur->purchase_date }}</td>
                                        <td>AED {{ number_format($pur->grand_total, 2) }}</td>
                                        <td class="text-success">AED {{ number_format($pur->paid_amount, 2) }}</td>
                                        <td class="text-end font-monospace text-danger fw-bold">AED {{ number_format($pur->due_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No individual unpaid bills found (balance may stem from opening balance).</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closePurchasesModal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
