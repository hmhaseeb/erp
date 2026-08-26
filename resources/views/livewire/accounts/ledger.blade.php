<div>
    <!-- Page Header -->
    <x-page-header title="Account Ledger Statement" subtitle="Detailed chronological audit statement and running balances for financial accounts.">
        <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
            <i class="bx bx-printer me-1"></i> Print Statement
        </button>
    </x-page-header>

    <!-- Filter Card -->
    <x-filter-card>
        <div class="col-lg-3 col-md-4">
            <label class="form-label font-size-12 text-muted mb-1">Select Account</label>
            <x-searchable-select wire:model.live="account_id" class="form-select" placeholder="Select Account...">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }})</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="col-lg-3 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">From Date</label>
            <input type="date" wire:model.live="start_date" class="form-control">
        </div>
        <div class="col-lg-3 col-md-3">
            <label class="form-label font-size-12 text-muted mb-1">To Date</label>
            <input type="date" wire:model.live="end_date" class="form-control">
        </div>
        <div class="col-lg-1 col-md-2">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </x-searchable-select>
        </div>
        <div class="col-lg-2 col-md-2">
            <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                <i class="bx bx-reset me-1"></i> Reset
            </button>
        </div>
        <x-slot:extra>
            <div class="col-12">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search within ledger transactions (description, type)...">
                </div>
            </div>
        </x-slot:extra>
    </x-filter-card>

    @if($selectedAccount)
        <!-- Ledger Summary KPI Row -->
        <div class="row mb-3">
            <x-kpi-card col="col-md-3" title="Period Opening Balance" :value="number_format($openingBalance, 2)" prefix="AED " color="dark" />
            <x-kpi-card col="col-md-3" title="Total Debits (Inflows +)" :value="'+ ' . number_format($totalDebits, 2)" prefix="AED " color="success" />
            <x-kpi-card col="col-md-3" title="Total Credits (Outflows -)" :value="'- ' . number_format($totalCredits, 2)" prefix="AED " color="danger" />
            <x-kpi-card col="col-md-3" title="Closing Balance" :value="number_format($closingBalance, 2)" prefix="AED " :color="$closingBalance >= 0 ? 'primary' : 'danger'" />
        </div>

        <!-- Ledger Statement Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                    <div>
                        <h5 class="card-title mb-0">{{ $selectedAccount->name }} — General Ledger</h5>
                        <small class="text-muted">Period: {{ $start_date }} to {{ $end_date }}</small>
                    </div>
                    <div class="font-size-13 text-muted">
                        Total Ledger Postings: <strong>{{ $transactions->total() }}</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-nowrap mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 110px;">Date</th>
                                <th>Transaction Type</th>
                                <th>Description / Particulars</th>
                                <th class="text-end text-success" style="width: 140px;">Debit (Inflow +)</th>
                                <th class="text-end text-danger" style="width: 140px;">Credit (Outflow -)</th>
                                <th class="text-end" style="width: 150px;">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Opening Balance Row -->
                            <tr class="table-light fw-bold">
                                <td>{{ $start_date }}</td>
                                <td><span class="badge badge-soft-secondary">OPENING</span></td>
                                <td>Brought Forward Opening Balance</td>
                                <td class="text-end">-</td>
                                <td class="text-end">-</td>
                                <td class="text-end font-monospace text-dark">AED {{ number_format($openingBalance, 2) }}</td>
                            </tr>

                            @php
                                $running = $openingBalance;
                            @endphp

                            @forelse($transactions as $t)
                                @php
                                    $running = $running + (float)$t->debit - (float)$t->credit;
                                @endphp
                                <tr>
                                    <td>{{ $t->transaction_date }}</td>
                                    <td>
                                        <x-badge :type="in_array($t->transaction_type, ['Cash In', 'Bank Deposit', 'Income', 'Sale', 'Customer Payment']) ? 'success' : 'danger'">
                                            {{ $t->transaction_type }}
                                        </x-badge>
                                    </td>
                                    <td>{{ $t->description ?? '-' }}</td>
                                    <td class="text-end font-monospace {{ $t->debit > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                                        {{ $t->debit > 0 ? number_format($t->debit, 2) : '-' }}
                                    </td>
                                    <td class="text-end font-monospace {{ $t->credit > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ $t->credit > 0 ? number_format($t->credit, 2) : '-' }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold {{ $running >= 0 ? 'text-dark' : 'text-danger' }}">
                                        AED {{ number_format($running, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No transactions recorded in this date range.
                                    </td>
                                </tr>
                            @endforelse

                            <!-- Closing Balance Summary Row -->
                            <tr class="table-light fw-bold font-size-14">
                                <td colspan="3" class="text-end">Total Period Activity & Ending Balance:</td>
                                <td class="text-end text-success font-monospace">+ AED {{ number_format($totalDebits, 2) }}</td>
                                <td class="text-end text-danger font-monospace">- AED {{ number_format($totalCredits, 2) }}</td>
                                <td class="text-end text-primary font-monospace">AED {{ number_format($closingBalance, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Bar -->
                <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top">
                    <div class="text-muted font-size-13 mb-2 mb-sm-0">
                        Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} records
                    </div>
                    <div>
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
