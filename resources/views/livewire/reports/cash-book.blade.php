<div>
    <!-- Page Header -->
    <x-page-header title="Cash Book Register" subtitle="Consolidated chronological journal for all physical cash drawers and registers.">
        <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light w-100 w-sm-auto mt-2 mt-sm-0">
            <i class="bx bx-printer me-1"></i> Print Cash Book
        </button>
    </x-page-header>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Period Opening Cash" 
                :amount="$openingBalance" 
                prefix="AED " 
                color="secondary" 
                subtitle="Brought forward" 
                icon="bx-archive" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Total Cash Receipts (+)" 
                :amount="$totalDebits" 
                prefix="+ AED " 
                color="success" 
                subtitle="Cash inflows" 
                icon="bx-arrow-to-bottom" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Total Cash Disbursements (-)" 
                :amount="$totalCredits" 
                prefix="- AED " 
                color="danger" 
                subtitle="Cash outflows" 
                icon="bx-arrow-from-bottom" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Closing Cash Balance" 
                :amount="$closingBalance" 
                prefix="AED " 
                color="primary" 
                subtitle="Carried forward" 
                icon="bx-wallet" />
        </div>
    </div>

    <!-- Search & Filter Card -->
    <x-filter-card>
        <div class="col-12 col-md-6 col-lg-5">
            <label class="form-label font-size-12 text-muted mb-1">Search Cash Entries</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search particulars, type, drawer...">
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">From Date</label>
            <input type="date" wire:model.live="start_date" class="form-control">
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-3">
            <label class="form-label font-size-12 text-muted mb-1">To Date</label>
            <input type="date" wire:model.live="end_date" class="form-control">
        </div>
        <div class="col-6 col-sm-6 col-md-3 col-lg-1">
            <label class="form-label font-size-12 text-muted mb-1">Per Page</label>
            <x-searchable-select wire:model.live="perPage" class="form-select">
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </x-searchable-select>
        </div>
        <x-slot:extra>
            <div class="col-12 text-sm-end text-center mt-1">
                <button type="button" wire:click="resetFilters" class="btn btn-sm btn-light">
                    <i class="bx bx-reset me-1"></i> Reset Filters
                </button>
            </div>
        </x-slot:extra>
    </x-filter-card>

    <!-- Cash Book Table Card -->
    <x-table-card target="search, start_date, end_date, perPage, sortBy, resetFilters" loadingText="Loading cash entries..." :paginator="$transactions">
        <table class="table table-bordered align-middle table-nowrap mb-0 font-size-13">
            <thead class="table-light">
                <tr>
                    <th style="width: 110px;">Date</th>
                    <th style="min-width: 130px;">Cash Drawer</th>
                    <th>Transaction Type</th>
                    <th style="min-width: 150px;">Particulars / Description</th>
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
                        <td><x-badge type="info">{{ $t->account->name ?? 'Cash' }}</x-badge></td>
                        <td>
                            <x-badge :type="$t->debit > 0 ? 'success' : 'danger'">
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
                        <td colspan="7">
                            <x-empty-state 
                                icon="bx bx-wallet" 
                                title="No cash entries recorded" 
                                message="No cash entries match your selected period or search."
                                :search="$search" />
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
    </x-table-card>
</div>
