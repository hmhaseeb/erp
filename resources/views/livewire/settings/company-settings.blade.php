<div>
    <!-- Page Header -->
    <x-page-header title="Company Settings" subtitle="Configure business legal name, TRN tax number, contacts, and currency." />

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form wire:submit.prevent="saveSettings">
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">Company Trade Name</label>
                        <input type="text" wire:model="company_name" class="form-control">
                        @error('company_name') <span class="text-danger font-size-12">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">Legal Entity Name</label>
                        <input type="text" wire:model="legal_name" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label fw-semibold">{{ $this->taxNumberLabel }} Tax Number</label>
                        <input type="text" wire:model="trn_number" class="form-control" placeholder="{{ $this->taxNumberPlaceholder }}">
                        <span class="text-muted font-size-11">Business official tax registration ID ({{ $this->taxNumberLabel }}).</span>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label fw-semibold">Currency Selection</label>
                        <x-searchable-select wire:model.live="selected_preset" class="form-select" placeholder="Select Currency...">
                            @foreach($commonCurrencies as $code => $c)
                                <option value="{{ $code }}" @selected($selected_preset === $code)>{{ $code }} - {{ $c['name'] }} ({{ $c['symbol'] }})</option>
                            @endforeach
                            <option value="CUSTOM" @selected($selected_preset === 'CUSTOM')>Custom Currency...</option>
                        </x-searchable-select>
                        <span class="text-muted font-size-11">Choose a preset or customize code & symbol below.</span>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label fw-semibold">Default {{ $this->taxLabel }} %</label>
                        <div class="input-group">
                            <input type="number" step="0.01" wire:model="default_vat_percent" class="form-control" placeholder="{{ $this->defaultTaxPlaceholder }}">
                            <span class="input-group-text">%</span>
                        </div>
                        <span class="text-muted font-size-11">Default tax applied on new products, sales, and purchases.</span>
                    </div>
                </div>

                <div class="row bg-light rounded p-3 mb-3 border align-items-center">
                    <div class="col-12 col-sm-6 col-md-4 mb-2 mb-md-0">
                        <label class="form-label fw-semibold">Currency Code <span class="text-danger">*</span></label>
                        <input type="text" wire:model.live.debounce.300ms="currency" class="form-control text-uppercase font-monospace fw-bold" placeholder="e.g. AED, USD, PKR" maxlength="10">
                        @error('currency') <span class="text-danger font-size-12">{{ $message }}</span> @enderror
                        <span class="text-muted font-size-11 d-block mt-1">Shown in invoices, reports, and table headers.</span>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 mb-2 mb-md-0">
                        <label class="form-label fw-semibold">Currency Symbol <span class="text-danger">*</span></label>
                        <input type="text" wire:model.live.debounce.300ms="currency_symbol" class="form-control font-monospace fw-bold" placeholder="e.g. AED, $, Rs., €" maxlength="10">
                        @error('currency_symbol') <span class="text-danger font-size-12">{{ $message }}</span> @enderror
                        <span class="text-muted font-size-11 d-block mt-1">Optional symbol prefix for display.</span>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-2 border rounded bg-white shadow-sm">
                            <span class="text-muted font-size-11 text-uppercase fw-semibold d-block">Display Preview Across System</span>
                            <div class="d-flex align-items-baseline gap-2 mt-1">
                                <span class="badge bg-primary-subtle text-primary font-monospace fs-6 px-2 py-1">
                                    {{ $currency ?: 'AED' }} 1,250.00
                                </span>
                                @if($currency_symbol && $currency_symbol !== $currency)
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace fs-6 px-2 py-1">
                                        {{ $currency_symbol }} 1,250.00
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">Office Phone</label>
                        <input type="text" wire:model="phone" class="form-control">
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" wire:model="mobile" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">Company Email</label>
                        <input type="email" wire:model="email" class="form-control">
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">Website</label>
                        <input type="text" wire:model="website" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" wire:model="city" class="form-control">
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" wire:model.live.debounce.300ms="country" list="country_list" class="form-control" placeholder="e.g. India, United Arab Emirates">
                        <datalist id="country_list">
                            <option value="India">
                            <option value="United Arab Emirates">
                            <option value="Saudi Arabia">
                            <option value="Oman">
                            <option value="Qatar">
                            <option value="Kuwait">
                            <option value="Bahrain">
                            <option value="United States">
                            <option value="United Kingdom">
                            <option value="Pakistan">
                        </datalist>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Address</label>
                    <textarea wire:model="address" class="form-control" rows="2"></textarea>
                </div>

                <div class="text-sm-end text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4 w-100 w-sm-auto">
                        <i class="bx bx-save me-1"></i> Save Company Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
