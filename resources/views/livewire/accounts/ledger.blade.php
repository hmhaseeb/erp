<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Account Ledger Statement</h4>
                    <p class="text-muted font-size-13 mb-0">Detailed chronological audit statement and running balances for financial accounts.</p>
                </div>
                <div class="page-title-right">
                    <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
                        <i class="bx bx-printer me-1"></i> Print Statement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-4">
                    <label class="form-label font-size-12 text-muted mb-1">Select Account</label>
                    <select wire:model.live="account_id" class="form-select">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">From Date</label>
                    <input type="date" wire:model.live="start_date" class="form-control">
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">To Date</label>
                    <input type="date" wire:model.live="end_date" class="form-control">
                </div>
                <div class="col-lg-2 col-md-2">
                    <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                        <i class="bx bx-reset me-1"></i> Reset
                    </button>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search within ledger transactions (description, type)...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($selectedAccount)
        <!-- Ledger Summary KPI Row -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <span class="text-muted font-size-12 d-block">Period Opening Balance</span>
                        <h5 class="mb-0 font-monospace text-dark fw-bold">AED {{ number_format($openingBalance, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <span class="text-muted font-size-12 d-block">Total Debits (Inflows +)</span>
                        <h5 class="mb-0 font-monospace text-success fw-bold">+ AED {{ number_format($totalDebits, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <span class="text-muted font-size-12 d-block">Total Credits (Outflows -)</span>
                        <h5 class="mb-0 font-monospace text-danger fw-bold">- AED {{ number_format($totalCredits, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <span class="text-muted font-size-12 d-block">Closing Balance</span>
                        <h5 class="mb-0 font-monospace {{ $closingBalance >= 0 ? 'text-primary' : 'text-danger' }} fw-bold">
                            AED {{ number_format($closingBalance, 2) }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ledger Statement Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-title mb-0">{{ $selectedAccount->name }} — General Ledger</h5>
                        <small class="text-muted">Period: {{ $start_date }} to {{ $end_date }}</small>
                    </div>
                    <div class="font-size-13 text-muted">
                        Total Ledger Postings: <strong>{{ $transactions->count() }}</strong>
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
                                        <span class="badge {{ in_array($t->transaction_type, ['Cash In', 'Bank Deposit', 'Income', 'Sale', 'Customer Payment']) ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                            {{ $t->transaction_type }}
                                        </span>
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
            </div>
        </div>
    @endif
</div>
