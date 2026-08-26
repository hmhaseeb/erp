@if($selectedCustomer)
    <x-modal 
        :isOpen="$isViewModalOpen" 
        :title="'Customer Details — ' . $selectedCustomer->name" 
        size="modal-xl modal-dialog-centered"
        closeAction="closeViewModal">

        <!-- Top Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded text-center">
                    <span class="text-muted font-size-12 d-block mb-1">Customer Code</span>
                    <h5 class="mb-0 font-monospace text-primary fw-bold">{{ $selectedCustomer->customer_code }}</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded text-center">
                    <span class="text-muted font-size-12 d-block mb-1">Outstanding Receivable</span>
                    <h5 class="mb-0 font-monospace fw-bold {{ $selectedCustomer->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                        AED {{ number_format($selectedCustomer->current_balance, 2) }}
                    </h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded text-center">
                    <span class="text-muted font-size-12 d-block mb-1">Credit Limit</span>
                    <h5 class="mb-0 font-monospace text-dark fw-bold">AED {{ number_format($selectedCustomer->credit_limit, 2) }}</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded text-center">
                    <span class="text-muted font-size-12 d-block mb-1">Account Status</span>
                    <x-badge :type="$selectedCustomer->status ? 'success' : 'danger'">
                        {{ $selectedCustomer->status ? 'Active Client' : 'Inactive' }}
                    </x-badge>
                </div>
            </div>
        </div>

        <!-- Details & History Tabs -->
        <div x-data="{ activeTab: 'overview' }">
            <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link cursor-pointer" :class="{ 'active': activeTab === 'overview' }" @click="activeTab = 'overview'">
                        <i class="bx bx-user me-1"></i> Profile & Credit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link cursor-pointer" :class="{ 'active': activeTab === 'sales' }" @click="activeTab = 'sales'">
                        <i class="bx bx-receipt me-1"></i> Sales Invoices ({{ $selectedCustomer->sales->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link cursor-pointer" :class="{ 'active': activeTab === 'payments' }" @click="activeTab = 'payments'">
                        <i class="bx bx-dollar-circle me-1"></i> Receipt Vouchers ({{ $selectedCustomer->payments->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link cursor-pointer" :class="{ 'active': activeTab === 'ledger' }" @click="activeTab = 'ledger'">
                        <i class="bx bx-list-ul me-1"></i> Ledger Activity ({{ $selectedCustomer->transactions->count() }})
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'">
                    <div class="row g-3 font-size-13">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th class="text-muted" style="width: 140px;">Customer Name:</th>
                                    <td class="fw-semibold text-dark">{{ $selectedCustomer->name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Company Name:</th>
                                    <td>{{ $selectedCustomer->company_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Contact Person:</th>
                                    <td>{{ $selectedCustomer->contact_person ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Mobile / Phone:</th>
                                    <td>{{ $selectedCustomer->mobile ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Email Address:</th>
                                    <td>{{ $selectedCustomer->email ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th class="text-muted" style="width: 140px;">TRN / Tax Number:</th>
                                    <td><code>{{ $selectedCustomer->trn_number ?? '-' }}</code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Credit Limit:</th>
                                    <td class="font-monospace text-primary fw-semibold">AED {{ number_format($selectedCustomer->credit_limit, 2) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Opening Balance:</th>
                                    <td class="font-monospace">AED {{ number_format($selectedCustomer->opening_balance, 2) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Payment Terms:</th>
                                    <td>{{ $selectedCustomer->payment_terms ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Address:</th>
                                    <td>{{ $selectedCustomer->address ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        @if($selectedCustomer->notes)
                            <div class="col-12 mt-2">
                                <div class="p-3 bg-light rounded">
                                    <strong class="font-size-12 text-muted d-block mb-1">Notes / Remarks:</strong>
                                    <p class="mb-0 font-size-13 text-secondary">{{ $selectedCustomer->notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sales Invoices Tab -->
                <div x-show="activeTab === 'sales'">
                    <div class="table-responsive" style="max-height: 320px;">
                        <table class="table table-hover table-sm align-middle table-nowrap mb-0 font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Payment Type</th>
                                    <th class="text-end">Grand Total</th>
                                    <th class="text-end">Paid Amount</th>
                                    <th class="text-end">Due Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($selectedCustomer->sales as $sale)
                                    <tr>
                                        <td><code>{{ $sale->invoice_number }}</code></td>
                                        <td>{{ $sale->sale_date }}</td>
                                        <td><x-badge type="info">{{ $sale->payment_type }}</x-badge></td>
                                        <td class="text-end font-monospace">AED {{ number_format($sale->grand_total, 2) }}</td>
                                        <td class="text-end font-monospace text-success">AED {{ number_format($sale->paid_amount, 2) }}</td>
                                        <td class="text-end font-monospace fw-bold text-danger">AED {{ number_format($sale->due_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">No sales invoices recorded for this customer.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Receipt Vouchers Tab -->
                <div x-show="activeTab === 'payments'">
                    <div class="table-responsive" style="max-height: 320px;">
                        <table class="table table-hover table-sm align-middle table-nowrap mb-0 font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Payment Date</th>
                                    <th>Deposited Into</th>
                                    <th>Reference #</th>
                                    <th class="text-end">Amount Received</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($selectedCustomer->payments as $pmt)
                                    <tr>
                                        <td><code>{{ $pmt->payment_number }}</code></td>
                                        <td>{{ $pmt->payment_date }}</td>
                                        <td><x-badge type="info">{{ $pmt->account->name ?? 'Bank/Cash' }}</x-badge></td>
                                        <td>{{ $pmt->reference_number ?? '-' }}</td>
                                        <td class="text-end font-monospace text-success fw-bold font-size-14">AED {{ number_format($pmt->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No payment receipt vouchers recorded for this customer.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ledger Activity Tab -->
                <div x-show="activeTab === 'ledger'">
                    <div class="table-responsive" style="max-height: 320px;">
                        <table class="table table-hover table-sm align-middle table-nowrap mb-0 font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-end text-danger">Debit / Sale (+)</th>
                                    <th class="text-end text-success">Credit / Paid (-)</th>
                                    <th class="text-end">Running Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($selectedCustomer->transactions as $tx)
                                    <tr>
                                        <td>{{ $tx->date }}</td>
                                        <td><x-badge :type="$tx->debit > 0 ? 'warning' : 'success'">{{ $tx->transaction_type }}</x-badge></td>
                                        <td>{{ $tx->description ?? '-' }}</td>
                                        <td class="text-end font-monospace {{ $tx->debit > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ $tx->debit > 0 ? 'AED ' . number_format($tx->debit, 2) : '-' }}
                                        </td>
                                        <td class="text-end font-monospace {{ $tx->credit > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                                            {{ $tx->credit > 0 ? 'AED ' . number_format($tx->credit, 2) : '-' }}
                                        </td>
                                        <td class="text-end font-monospace fw-bold">AED {{ number_format($tx->balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">No ledger transactions recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:footer>
            <div class="d-flex justify-content-between align-items-center w-100">
                <a href="{{ route('payments.customer') }}" class="btn btn-sm btn-outline-success">
                    <i class="bx bx-dollar-circle me-1"></i> Record Receipt Voucher
                </a>
                <button type="button" class="btn btn-light" wire:click="closeViewModal">Close</button>
            </div>
        </x-slot:footer>
    </x-modal>
@endif
