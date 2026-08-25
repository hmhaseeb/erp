<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Cash Book Register</h4>
                    <p class="text-muted font-size-13 mb-0">Consolidated chronological journal for all physical cash drawers and registers.</p>
                </div>
                <div class="page-title-right">
                    <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
                        <i class="bx bx-printer me-1"></i> Print Cash Book
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block">Period Opening Cash</span>
                    <h5 class="mb-0 font-monospace text-dark fw-bold">AED {{ number_format($openingBalance, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block">Total Cash Receipts (+)</span>
                    <h5 class="mb-0 font-monospace text-success fw-bold">+ AED {{ number_format($totalDebits, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block">Total Cash Disbursements (-)</span>
                    <h5 class="mb-0 font-monospace text-danger fw-bold">- AED {{ number_format($totalCredits, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <span class="text-muted font-size-12 d-block">Closing Cash Balance</span>
                    <h5 class="mb-0 font-monospace text-primary fw-bold">AED {{ number_format($closingBalance, 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label font-size-12 text-muted mb-1">Search Cash Entries</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search particulars, type, drawer...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">From Date</label>
                    <input type="date" wire:model.live="start_date" class="form-control">
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">To Date</label>
                    <input type="date" wire:model.live="end_date" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4">
                    <button type="button" wire:click="resetFilters" class="btn btn-light w-100">
                        <i class="bx bx-reset me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Book Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle table-nowrap mb-0 font-size-13">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 110px;">Date</th>
                            <th>Cash Drawer</th>
                            <th>Transaction Type</th>
                            <th>Particulars / Description</th>
                            <th class="text-end text-success" style="width: 140px;">Cash In (+)</th>
                            <th class="text-end text-danger" style="width: 140px;">Cash Out (-)</th>
                            <th class="text-end" style="width: 150px;">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Row -->
                        <tr class="table-light fw-bold">
                            <td>{{ $start_date }}</td>
                            <td colspan="3">Brought Forward Cash Balance</td>
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
                                <td><span class="badge badge-soft-info">{{ $t->account->name ?? 'Cash' }}</span></td>
                                <td>
                                    <span class="badge {{ $t->debit > 0 ? 'badge-soft-success' : 'badge-soft-danger' }}">
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
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No cash entries recorded for this period.
                                </td>
                            </tr>
                        @endforelse

                        <!-- Summary Footer Row -->
                        <tr class="table-light fw-bold font-size-14">
                            <td colspan="4" class="text-end">Total Cash Activity & Ending Balance:</td>
                            <td class="text-end text-success font-monospace">+ AED {{ number_format($totalDebits, 2) }}</td>
                            <td class="text-end text-danger font-monospace">- AED {{ number_format($totalCredits, 2) }}</td>
                            <td class="text-end text-primary font-monospace">AED {{ number_format($closingBalance, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
