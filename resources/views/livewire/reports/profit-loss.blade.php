<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Profit & Loss Statement (Income Statement)</h4>
                    <p class="text-muted font-size-13 mb-0">Financial performance summary calculating Revenue, Cost of Goods Sold, Gross Profit, and Net Profit.</p>
                </div>
                <div class="page-title-right">
                    <button onclick="window.print()" class="btn btn-secondary waves-effect waves-light">
                        <i class="bx bx-printer me-1"></i> Print P&L Statement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">From Date</label>
                    <input type="date" wire:model.live="start_date" class="form-control">
                </div>
                <div class="col-lg-3 col-md-3">
                    <label class="form-label font-size-12 text-muted mb-1">To Date</label>
                    <input type="date" wire:model.live="end_date" class="form-control">
                </div>
                <div class="col-lg-6 col-md-6 text-md-end">
                    <div class="btn-group" role="group">
                        <button type="button" wire:click="setPeriod('today')" class="btn btn-sm btn-outline-primary">Today</button>
                        <button type="button" wire:click="setPeriod('this_month')" class="btn btn-sm btn-outline-primary">This Month</button>
                        <button type="button" wire:click="setPeriod('last_month')" class="btn btn-sm btn-outline-primary">Last Month</button>
                        <button type="button" wire:click="setPeriod('this_year')" class="btn btn-sm btn-outline-primary">This Year</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit & Loss Statement Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1">STATEMENT OF PROFIT AND LOSS</h4>
                <p class="text-muted font-size-13 mb-0">For the period from <strong>{{ $start_date }}</strong> to <strong>{{ $end_date }}</strong></p>
                <small class="text-muted">All amounts in UAE Dirhams (AED)</small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle font-size-14 mb-0">
                    <tbody>
                        <!-- 1. Operating Revenue -->
                        <tr class="table-light">
                            <td colspan="2" class="fw-bold text-dark font-size-15">
                                <i class="bx bx-chevron-right text-primary me-1"></i> 1. Operating Revenue
                            </td>
                            <td class="text-end fw-bold text-dark" style="width: 220px;">Amount (AED)</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2.5rem;">Gross Sales (Revenue from Goods Sold)</td>
                            <td class="text-end text-muted font-monospace">AED {{ number_format($report['revenue']['gross_sales'], 2) }}</td>
                            <td class="text-end"></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2.5rem;">Less: Sales Returns & Allowances</td>
                            <td class="text-end text-danger font-monospace">- AED {{ number_format($report['revenue']['sales_returns'], 2) }}</td>
                            <td class="text-end"></td>
                        </tr>
                        <tr class="fw-bold">
                            <td style="padding-left: 1.5rem;" class="text-dark">Net Sales Revenue</td>
                            <td></td>
                            <td class="text-end font-monospace text-success font-size-15">
                                AED {{ number_format($report['revenue']['net_sales'], 2) }}
                            </td>
                        </tr>

                        <!-- 2. Cost of Goods Sold (COGS) -->
                        <tr class="table-light">
                            <td colspan="2" class="fw-bold text-dark font-size-15">
                                <i class="bx bx-chevron-right text-primary me-1"></i> 2. Cost of Goods Sold (COGS)
                            </td>
                            <td class="text-end"></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2.5rem;">Total Purchases in Period</td>
                            <td class="text-end text-muted font-monospace">AED {{ number_format($report['cogs']['purchases'], 2) }}</td>
                            <td class="text-end"></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2.5rem;">Less: Purchase Returns & Allowances</td>
                            <td class="text-end text-success font-monospace">- AED {{ number_format($report['cogs']['purchase_returns'], 2) }}</td>
                            <td class="text-end"></td>
                        </tr>
                        <tr class="fw-bold">
                            <td style="padding-left: 1.5rem;" class="text-dark">Net Cost of Goods Sold</td>
                            <td></td>
                            <td class="text-end font-monospace text-danger font-size-15">
                                (AED {{ number_format($report['cogs']['net_purchases'], 2) }})
                            </td>
                        </tr>

                        <!-- GROSS PROFIT ROW -->
                        <tr class="table-primary fw-bold font-size-16">
                            <td colspan="2" class="text-dark">
                                <i class="bx bx-trophy text-primary me-1"></i> GROSS PROFIT (Net Sales - COGS)
                            </td>
                            <td class="text-end font-monospace {{ $report['gross_profit'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                AED {{ number_format($report['gross_profit'], 2) }}
                            </td>
                        </tr>

                        <!-- 3. Other Operating Income -->
                        <tr class="table-light">
                            <td colspan="2" class="fw-bold text-dark font-size-15">
                                <i class="bx bx-chevron-right text-primary me-1"></i> 3. Other Operating Income
                            </td>
                            <td class="text-end"></td>
                        </tr>
                        @forelse($report['other_income_breakdown'] as $category => $amount)
                            <tr>
                                <td style="padding-left: 2.5rem;">{{ $category }}</td>
                                <td class="text-end text-muted font-monospace">AED {{ number_format($amount, 2) }}</td>
                                <td class="text-end"></td>
                            </tr>
                        @empty
                            <tr>
                                <td style="padding-left: 2.5rem;" class="text-muted">No miscellaneous income in period</td>
                                <td class="text-end text-muted font-monospace">AED 0.00</td>
                                <td class="text-end"></td>
                            </tr>
                        @endforelse
                        <tr class="fw-bold">
                            <td style="padding-left: 1.5rem;" class="text-dark">Total Other Income</td>
                            <td></td>
                            <td class="text-end font-monospace text-success font-size-15">
                                + AED {{ number_format($report['other_income'], 2) }}
                            </td>
                        </tr>

                        <!-- 4. Operating Expenses -->
                        <tr class="table-light">
                            <td colspan="2" class="fw-bold text-dark font-size-15">
                                <i class="bx bx-chevron-right text-primary me-1"></i> 4. Operating Expenses (Overheads)
                            </td>
                            <td class="text-end"></td>
                        </tr>
                        @forelse($report['expenses_breakdown'] as $category => $amount)
                            <tr>
                                <td style="padding-left: 2.5rem;">{{ $category }}</td>
                                <td class="text-end text-muted font-monospace">AED {{ number_format($amount, 2) }}</td>
                                <td class="text-end"></td>
                            </tr>
                        @empty
                            <tr>
                                <td style="padding-left: 2.5rem;" class="text-muted">No operating expenses recorded in period</td>
                                <td class="text-end text-muted font-monospace">AED 0.00</td>
                                <td class="text-end"></td>
                            </tr>
                        @endforelse
                        <tr class="fw-bold">
                            <td style="padding-left: 1.5rem;" class="text-dark">Total Operating Expenses</td>
                            <td></td>
                            <td class="text-end font-monospace text-danger font-size-15">
                                (AED {{ number_format($report['expenses'], 2) }})
                            </td>
                        </tr>

                        <!-- FINAL NET PROFIT / NET LOSS ROW -->
                        <tr class="{{ $report['net_profit'] >= 0 ? 'table-success' : 'table-danger' }} fw-bold font-size-18">
                            <td colspan="2" class="text-dark">
                                <i class="bx {{ $report['net_profit'] >= 0 ? 'bx-check-circle text-success' : 'bx-x-circle text-danger' }} me-1"></i>
                                FINAL NET {{ $report['net_profit'] >= 0 ? 'PROFIT' : 'LOSS' }}
                            </td>
                            <td class="text-end font-monospace {{ $report['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                AED {{ number_format($report['net_profit'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
