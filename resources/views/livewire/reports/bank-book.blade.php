<div>
    <!-- Page Header -->
    <x-page-header title="Bank Book Statement" subtitle="Reconciliation and journal records for company banking accounts, cheques, and transfers.">
        <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
            <i class="bx bx-printer me-1"></i> Print Bank Statement
        </button>
    </x-page-header>

    <!-- Filter Card -->
    <x-filter-card>
        <div class="col-lg-3 col-md-4">
            <label class="form-label font-size-12 text-muted mb-1">Select Bank Account</label>
            <x-searchable-select wire:model.live="account_id" class="form-select">
                @foreach($bankAccounts as $bank)
                    <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->bank_name ?? 'Bank' }})</option>
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0" placeholder="Search within bank transactions (description, cheques, transfers)...">
                </div>
            </div>
        </x-slot:extra>
    </x-filter-card>

    @if($selectedAccount)
        <!-- KPI Summary Row -->
        <div class="row mb-3">
            <div class="col-md-3">
                <x-kpi-card 
                    title="Period Opening Balance" 
                    :amount="$openingBalance" 
                    prefix="AED " 
                    color="secondary" 
                    subtitle="Brought forward" 
                    icon="bx-archive" />
            </div>
            <div class="col-md-3">
                <x-kpi-card 
                    title="Total Bank Deposits (+)" 
                    :amount="$totalDebits" 
                    prefix="+ AED " 
                    color="success" 
                    subtitle="Account deposits" 
                    icon="bx-arrow-to-bottom" />
            </div>
            <div class="col-md-3">
                <x-kpi-card 
                    title="Total Withdrawals (-)" 
                    :amount="$totalCredits" 
                    prefix="- AED " 
                    color="danger" 
                    subtitle="Account disbursements" 
                    icon="bx-arrow-from-bottom" />
            </div>
            <div class="col-md-3">
                <x-kpi-card 
                    title="Closing Bank Balance" 
                    :amount="$closingBalance" 
                    prefix="AED " 
                    color="primary" 
                    subtitle="Carried forward" 
                    icon="bx-wallet" />
            </div>
        </div>

        <!-- Bank Statement Table Card -->
        <x-table-card target="search, start_date, end_date, perPage, sortBy, resetFilters" loadingText="Loading bank transactions..." :paginator="$transactions">
            <table class="table table-bordered align-middle table-nowrap mb-0 font-size-13">
                <thead class="table-light">
                    <tr>
                        <th style="width: 110px;">Date</th>
                        <th>Transaction Type</th>
                        <th>Particulars / Description</th>
                        <th class="text-end text-success" style="width: 140px;">Deposits (+)</th>
                        <th class="text-end text-danger" style="width: 140px;">Withdrawals (-)</th>
                        <th class="text-end" style="width: 150px;">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Opening Row -->
                    <tr class="table-light fw-bold">
                        <td>{{ $start_date }}</td>
                        <td colspan="2">Brought Forward Bank Balance</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td class="text-end font-monospace text-dark">AED {{ number_format($openingBalance, 2) }}</td>
                    </tr>

                    @php
                        $running = $openingBalance;
                    @endphp

                    @if($transactions)
                        @forelse($transactions as $t)
                            @php
                                $running = $running + (float)$t->debit - (float)$t->credit;
                            @endphp
                            <tr>
                                <td>{{ $t->transaction_date }}</td>
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
                                <td colspan="6">
                                    <x-empty-state 
                                        icon="bx bx-buildings" 
                                        title="No bank transactions recorded" 
                                        message="No bank transactions match your selected period or search."
                                        :search="$search" />
                                </td>
                            </tr>
                        @endforelse
                    @endif

                    <!-- Summary Footer Row -->
                    <tr class="table-light fw-bold font-size-14">
                        <td colspan="3" class="text-end">Total Period Activity & Ending Balance:</td>
                        <td class="text-end text-success font-monospace">+ AED {{ number_format($totalDebits, 2) }}</td>
                        <td class="text-end text-danger font-monospace">- AED {{ number_format($totalCredits, 2) }}</td>
                        <td class="text-end text-primary font-monospace">AED {{ number_format($closingBalance, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </x-table-card>
    @endif
</div>
