<div>
    <!-- Page Header -->
    <x-page-header title="General System Settings" subtitle="Configure numbering prefixes, timezones, date formatting, and financial account defaults." />

    <!-- Flash Alert -->
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle font-size-20 me-2"></i>
                <div class="flex-grow-1 font-size-13">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Live Time & Date Based Preview Banner -->
    <div class="card border border-primary-subtle shadow-sm mb-4 rounded-3 overflow-hidden">
        <div class="card-header bg-primary bg-opacity-10 py-3 px-4 border-bottom border-primary-subtle d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <span class="avatar-xs rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center">
                        <i class="bx bx-time-five font-size-16"></i>
                    </span>
                    Live System Clock & Localization Preview
                </h5>
                <p class="text-muted font-size-12 mb-0">System timestamps, PDF prints, and transaction logs will render according to these settings.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" wire:click="loadDefaults(true)" class="btn btn-outline-secondary btn-sm shadow-sm font-size-12">
                    <i class="bx bx-reset me-1"></i> Load Recommended Defaults
                </button>
            </div>
        </div>
        <div class="card-body p-4 bg-light bg-opacity-25">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="d-flex align-items-center gap-3 p-3 bg-white rounded border">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded bg-primary-subtle text-primary font-size-22">
                                <i class="bx bx-calendar-event"></i>
                            </span>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-muted font-size-11 fw-semibold text-uppercase">Current Date ({{ $date_format }})</div>
                            <div class="fw-bold text-dark font-size-16 font-monospace text-truncate">{{ $this->formattedCurrentDate }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="d-flex align-items-center gap-3 p-3 bg-white rounded border">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded bg-info-subtle text-info font-size-22">
                                <i class="bx bx-time"></i>
                            </span>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-muted font-size-11 fw-semibold text-uppercase">Current Time ({{ $time_zone }})</div>
                            <div class="fw-bold text-dark font-size-16 font-monospace text-truncate">{{ $this->formattedCurrentTime }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="d-flex align-items-center gap-3 p-3 bg-white rounded border">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded bg-success-subtle text-success font-size-22">
                                <i class="bx bx-purchase-tag"></i>
                            </span>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-muted font-size-11 fw-semibold text-uppercase">Sample Codes & Decimal Precision</div>
                            <div class="font-size-12 text-dark font-monospace text-truncate">
                                <span class="badge bg-light text-dark border">{{ $product_prefix ?: 'PRD-' }}001</span>
                                <span class="badge bg-light text-dark border">{{ $customer_prefix ?: 'CUST-' }}001</span>
                                <span class="badge bg-light text-dark border">1,250.{{ str_repeat('0', max(0, min(4, (int)$decimal_places))) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 font-size-12 text-muted">
                <i class="bx bx-info-circle me-1 text-primary"></i> <strong>Full timestamp:</strong> {{ $this->fullDateTimePreview }}
            </div>
        </div>
    </div>

    <!-- Main Configuration Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h5 class="card-title fw-bold mb-0">System Preferences</h5>
            <p class="text-muted font-size-12 mb-0">Set your local timezone, date format, master data prefixes, and default accounts.</p>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="saveSettings">
                <!-- Section 1: Date & Time Localization -->
                <div class="mb-4">
                    <h6 class="text-uppercase fw-bold text-primary font-size-12 mb-3 pb-2 border-bottom">
                        <i class="bx bx-globe me-1"></i> Date & Time Localization
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold font-size-13">Date Format <span class="text-danger">*</span></label>
                            <x-searchable-select wire:model.live="date_format" class="form-select">
                                <option value="Y-m-d">YYYY-MM-DD (e.g. {{ date('Y-m-d') }}) — ISO Standard</option>
                                <option value="d/m/Y">DD/MM/YYYY (e.g. {{ date('d/m/Y') }}) — British / Gulf Standard</option>
                                <option value="m/d/Y">MM/DD/YYYY (e.g. {{ date('m/d/Y') }}) — US Standard</option>
                                <option value="d-M-Y">DD-Mon-YYYY (e.g. {{ date('d-M-Y') }}) — Abbreviated Month</option>
                                <option value="d F Y">DD Month YYYY (e.g. {{ date('d F Y') }}) — Full Text Date</option>
                            </x-searchable-select>
                            <small class="text-muted font-size-11 d-block mt-1">Controls how dates display across invoices, purchases, and ledger reports.</small>
                        </div>

                        <div class="col-12 col-md-5">
                            <label class="form-label fw-bold font-size-13">Timezone <span class="text-danger">*</span></label>
                            <x-searchable-select wire:model.live="time_zone" class="form-select">
                                @foreach($timezonesGrouped as $region => $tzList)
                                    <optgroup label="{{ $region }}">
                                        @foreach($tzList as $tzKey => $tzLabel)
                                            <option value="{{ $tzKey }}">{{ $tzLabel }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </x-searchable-select>
                            <small class="text-muted font-size-11 d-block mt-1">
                                <i class="bx bx-check text-success me-1"></i> Current timezone clock: <span class="fw-bold font-monospace text-dark">{{ $this->formattedCurrentTime }}</span>
                            </small>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold font-size-13">Currency Decimal Places <span class="text-danger">*</span></label>
                            <x-searchable-select wire:model.live="decimal_places" class="form-select">
                                <option value="0">0 (No Decimals • e.g. 1,250)</option>
                                <option value="2">2 (Standard • e.g. 1,250.00)</option>
                                <option value="3">3 (Precision • e.g. 1,250.000)</option>
                                <option value="4">4 (High Precision • e.g. 1,250.0000)</option>
                            </x-searchable-select>
                            <small class="text-muted font-size-11 d-block mt-1">Determines decimal precision on prices & totals.</small>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Master Data Auto-Numbering Prefixes -->
                <div class="mb-4">
                    <h6 class="text-uppercase fw-bold text-primary font-size-12 mb-3 pb-2 border-bottom">
                        <i class="bx bx-barcode me-1"></i> Master Data Code Prefixes
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold font-size-13">Product SKU Prefix</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted font-size-12"><i class="bx bx-box"></i></span>
                                <input type="text" wire:model.live="product_prefix" class="form-control font-monospace" placeholder="PRD-">
                            </div>
                            <small class="text-muted font-size-11 d-block mt-1">Sample product SKU: <span class="text-primary fw-semibold">{{ $product_prefix ?: 'PRD-' }}0001</span></small>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold font-size-13">Supplier Code Prefix</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted font-size-12"><i class="bx bx-user-pin"></i></span>
                                <input type="text" wire:model.live="supplier_prefix" class="form-control font-monospace" placeholder="SUP-">
                            </div>
                            <small class="text-muted font-size-11 d-block mt-1">Sample vendor code: <span class="text-primary fw-semibold">{{ $supplier_prefix ?: 'SUP-' }}0001</span></small>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold font-size-13">Customer Code Prefix</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted font-size-12"><i class="bx bx-user"></i></span>
                                <input type="text" wire:model.live="customer_prefix" class="form-control font-monospace" placeholder="CUST-">
                            </div>
                            <small class="text-muted font-size-11 d-block mt-1">Sample client code: <span class="text-primary fw-semibold">{{ $customer_prefix ?: 'CUST-' }}0001</span></small>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Default Financial Accounts -->
                <div class="mb-4">
                    <h6 class="text-uppercase fw-bold text-primary font-size-12 mb-3 pb-2 border-bottom">
                        <i class="bx bx-credit-card me-1"></i> Default Financial Accounts
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold font-size-13">Default Cash Account</label>
                            <x-searchable-select wire:model="default_cash_account_id" class="form-select">
                                <option value="">-- None --</option>
                                @foreach($accounts->where('type', 'Cash') as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} (Cash Drawer)</option>
                                @endforeach
                                @foreach($accounts->where('type', '!=', 'Cash') as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }})</option>
                                @endforeach
                            </x-searchable-select>
                            <small class="text-muted font-size-11 d-block mt-1">Used automatically for cash sales and petty cash expense disbursements.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold font-size-13">Default Bank Account</label>
                            <x-searchable-select wire:model="default_bank_account_id" class="form-select">
                                <option value="">-- None --</option>
                                @foreach($accounts->where('type', 'Bank') as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->bank_name ?: 'Bank' }})</option>
                                @endforeach
                                @foreach($accounts->where('type', '!=', 'Bank') as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }})</option>
                                @endforeach
                            </x-searchable-select>
                            <small class="text-muted font-size-11 d-block mt-1">Used automatically for bank transfer payments, cheques, and credit receipts.</small>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Inventory Operations Rules -->
                <div class="mb-4">
                    <h6 class="text-uppercase fw-bold text-primary font-size-12 mb-3 pb-2 border-bottom">
                        <i class="bx bx-cog me-1"></i> Inventory Operations
                    </h6>
                    <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                        <input class="form-check-input ms-0" type="checkbox" wire:model="allow_negative_stock" id="negativeStockSwitch" style="width: 2.5em; height: 1.3em;">
                        <label class="form-check-label fw-semibold font-size-13 cursor-pointer" for="negativeStockSwitch">
                            Allow Negative Inventory Stocking during Sales
                            <span class="d-block text-muted fw-normal font-size-11">If enabled, the system permits creating sales invoices even when recorded warehouse stock quantity is zero or insufficient.</span>
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
                    <span class="text-muted font-size-12">
                        <i class="bx bx-check-double text-success me-1"></i> Changes take effect immediately across all transaction modules.
                    </span>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-semibold">
                        <i class="bx bx-save me-1"></i> Save General Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
