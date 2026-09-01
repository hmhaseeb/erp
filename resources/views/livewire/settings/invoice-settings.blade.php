<div>
    <!-- Page Header -->
    <x-page-header title="Invoice & Numbering Settings" subtitle="Configure automatic yearly serial numbering, document prefixes, PDF layout, and billing terms.">
        <x-badge type="primary" size="font-size-13 py-2 px-3">
            <i class="bx bx-calendar me-1"></i> Current Year: {{ $currentYear }}
        </x-badge>
    </x-page-header>

    <!-- Top Status / Year Overview KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Current Year -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Current Year" 
                :value="$currentYear" 
                subtitle="Active Year Period" 
                color="primary" 
                icon="bx bx-calendar" />
        </div>

        <!-- Next Sales Invoice Number -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Next Sales Invoice #" 
                :value="$nextCurrentYearNumber" 
                :subtitle="$currentYearSalesCount . ' Invoices created in ' . $currentYear" 
                color="success" 
                icon="bx bx-receipt" />
        </div>

        <!-- Last / Previous Year -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                :title="'Last Year (' . $previousYear . ')'" 
                :value="$previousYearSalesCount . ' Invoices'" 
                :subtitle="'Final #: ' . $previousYearLastNumber" 
                color="info" 
                icon="bx bx-history" />
        </div>

        <!-- Yearly Reset Engine Status -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="Yearly Reset Status" 
                value="Auto-Reset Active" 
                subtitle="Resets to 0001 on Jan 1st" 
                color="success" 
                icon="bx bx-check-shield" />
        </div>
    </div>

    <!-- Yearly Sequence Explanation Banner -->
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4 p-3" role="alert">
        <i class="bx bx-info-circle font-size-24 me-3 text-info flex-shrink-0"></i>
        <div class="font-size-13">
            <strong>Automatic Yearly Reset Engine:</strong> All transaction document numbers automatically embed the active transaction year in the format 
            <code>[PREFIX]-[YEAR]-[0001]</code>. When a new calendar year begins, 
            every sequence resets automatically to <code>0001</code> (or your configured starting number), while all previous years' records remain completely preserved and untouched.
        </div>
    </div>

    <form wire:submit.prevent="saveSettings">
        <!-- Common Yearly Numbering Configuration -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-size-15 fw-bold">
                    <i class="bx bx-cog align-middle me-1 text-primary"></i> General Sequence Rules & PDF Page Layout
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label font-size-13 fw-semibold">Starting Sequence Number per Year <span class="text-danger">*</span></label>
                        <input type="number" min="1" wire:model.live.debounce.300ms="starting_number" class="form-control @error('starting_number') is-invalid @enderror" placeholder="1">
                        @error('starting_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted font-size-11">Initial sequence number each calendar year (default: <code>1</code> which pads to <code>0001</code>)</small>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label font-size-13 fw-semibold">Paper Size for PDF Invoices & Vouchers <span class="text-danger">*</span></label>
                        <x-searchable-select wire:model="paper_size" class="form-select {{ $errors->has('paper_size') ? 'is-invalid' : '' }}">
                            <option value="A4">A4 (Standard 210mm x 297mm)</option>
                            <option value="A5">A5 (Compact 148mm x 210mm)</option>
                            <option value="Letter">US Letter</option>
                        </x-searchable-select>
                        @error('paper_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Unified Document Serial Prefixes & Yearly Sequences Grid -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-size-15 fw-bold">
                    <i class="bx bx-hash align-middle me-1 text-primary"></i> Document Serial Prefixes & Yearly Sequences
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- 1. Sales Invoice -->
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="p-3 border rounded h-100 bg-white shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label font-size-13 fw-bold mb-0 text-dark">
                                    <i class="bx bx-receipt text-primary me-1"></i> Sales Invoice Prefix <span class="text-danger">*</span>
                                </label>
                                <span class="badge bg-primary-subtle text-primary font-size-11">Sales</span>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="invoice_prefix" class="form-control form-control-sm @error('invoice_prefix') is-invalid @enderror" placeholder="INV-">
                            @error('invoice_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <div class="mt-2 pt-2 border-top font-size-12">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>{{ $currentYear }}:</span>
                                    <code class="text-primary fw-bold">{{ $nextCurrentYearNumber }}</code>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>{{ $nextYear }} Start:</span>
                                    <code class="text-success">{{ $nextYearSales }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Purchase Invoice -->
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="p-3 border rounded h-100 bg-white shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label font-size-13 fw-bold mb-0 text-dark">
                                    <i class="bx bx-cart text-primary me-1"></i> Purchase Invoice Prefix <span class="text-danger">*</span>
                                </label>
                                <span class="badge bg-info-subtle text-info font-size-11">Purchases</span>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="purchase_prefix" class="form-control form-control-sm @error('purchase_prefix') is-invalid @enderror" placeholder="PUR-">
                            @error('purchase_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <div class="mt-2 pt-2 border-top font-size-12">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>{{ $currentYear }}:</span>
                                    <code class="text-primary fw-bold">{{ $nextPurchaseNumber }}</code>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>{{ $nextYear }} Start:</span>
                                    <code class="text-success">{{ $nextYearPurchase }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Sales Return (Credit Note) -->
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="p-3 border rounded h-100 bg-white shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label font-size-13 fw-bold mb-0 text-dark">
                                    <i class="bx bx-undo text-primary me-1"></i> Sales Return Prefix
                                </label>
                                <span class="badge bg-warning-subtle text-warning font-size-11">Credit Note</span>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="sales_return_prefix" class="form-control form-control-sm @error('sales_return_prefix') is-invalid @enderror" placeholder="SR-">
                            @error('sales_return_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <div class="mt-2 pt-2 border-top font-size-12">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>{{ $currentYear }}:</span>
                                    <code class="text-primary fw-bold">{{ $nextSalesReturnNumber }}</code>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>{{ $nextYear }} Start:</span>
                                    <code class="text-success">{{ $nextYearSalesReturn }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Purchase Return (Debit Note) -->
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="p-3 border rounded h-100 bg-white shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label font-size-13 fw-bold mb-0 text-dark">
                                    <i class="bx bx-redo text-primary me-1"></i> Purchase Return Prefix
                                </label>
                                <span class="badge bg-secondary-subtle text-secondary font-size-11">Debit Note</span>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="purchase_return_prefix" class="form-control form-control-sm @error('purchase_return_prefix') is-invalid @enderror" placeholder="PR-">
                            @error('purchase_return_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <div class="mt-2 pt-2 border-top font-size-12">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>{{ $currentYear }}:</span>
                                    <code class="text-primary fw-bold">{{ $nextPurchaseReturnNumber }}</code>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>{{ $nextYear }} Start:</span>
                                    <code class="text-success">{{ $nextYearPurchaseReturn }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Customer Payment Receipt -->
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="p-3 border rounded h-100 bg-white shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label font-size-13 fw-bold mb-0 text-dark">
                                    <i class="bx bx-check-circle text-primary me-1"></i> Customer Receipt Prefix
                                </label>
                                <span class="badge bg-success-subtle text-success font-size-11">Receivable</span>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="customer_payment_prefix" class="form-control form-control-sm @error('customer_payment_prefix') is-invalid @enderror" placeholder="REC-">
                            @error('customer_payment_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <div class="mt-2 pt-2 border-top font-size-12">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>{{ $currentYear }}:</span>
                                    <code class="text-success fw-bold">{{ $nextCustomerPaymentNumber }}</code>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>{{ $nextYear }} Start:</span>
                                    <code class="text-success">{{ $nextYearCustomerPayment }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Supplier Payment Voucher -->
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="p-3 border rounded h-100 bg-white shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label font-size-13 fw-bold mb-0 text-dark">
                                    <i class="bx bx-money text-primary me-1"></i> Supplier Voucher Prefix
                                </label>
                                <span class="badge bg-danger-subtle text-danger font-size-11">Payable</span>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="supplier_payment_prefix" class="form-control form-control-sm @error('supplier_payment_prefix') is-invalid @enderror" placeholder="PAY-">
                            @error('supplier_payment_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <div class="mt-2 pt-2 border-top font-size-12">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>{{ $currentYear }}:</span>
                                    <code class="text-danger fw-bold">{{ $nextSupplierPaymentNumber }}</code>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>{{ $nextYear }} Start:</span>
                                    <code class="text-success">{{ $nextYearSupplierPayment }}</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: PDF Print Notes, Bank Details & Footer -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-size-15 fw-bold">
                    <i class="bx bx-printer align-middle me-1 text-primary"></i> Printed PDF Invoice Terms & Bank Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label font-size-13 fw-semibold">Terms & Conditions (Printed on PDF)</label>
                        <textarea wire:model="terms_conditions" class="form-control font-size-13" rows="3" placeholder="Standard business terms, warranty conditions, or payment policies..."></textarea>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label font-size-13 fw-semibold">Bank Payment Details (Printed on PDF)</label>
                        <textarea wire:model="bank_details" class="form-control font-size-13" rows="3" placeholder="Beneficiary Account Name, IBAN, Bank Name, SWIFT code..."></textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-size-13 fw-semibold">Invoice Footer Note</label>
                    <input type="text" wire:model="invoice_footer" class="form-control font-size-13" placeholder="Thank you for your business!">
                </div>
            </div>
            <div class="card-footer bg-white border-top py-3 text-sm-end text-center">
                <button type="submit" class="btn btn-primary px-4 w-100 w-sm-auto">
                    <span wire:loading.remove wire:target="saveSettings">
                        <i class="bx bx-save me-1"></i> Save Invoice & Numbering Settings
                    </span>
                    <span wire:loading wire:target="saveSettings">
                        <i class="bx bx-loader-alt bx-spin me-1"></i> Saving...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
