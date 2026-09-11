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
                        <label class="form-label fw-semibold">TRN / VAT Tax Number</label>
                        <input type="text" wire:model="trn_number" class="form-control" placeholder="e.g. 100234567890003">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label fw-semibold">Currency Selection</label>
                        <div x-data="searchableSelect" @click.away="open = false" class="position-relative">
                            <!-- Native Select -->
                            <select x-ref="nativeSelect" wire:model.live="selected_preset" class="d-none">
                                @foreach($commonCurrencies as $code => $c)
                                    <option value="{{ $code }}">{{ $code }} - {{ $c['name'] }} ({{ $c['symbol'] }})</option>
                                @endforeach
                                <option value="CUSTOM">Custom Currency...</option>
                            </select>

                            <!-- Custom Select Trigger -->
                            <div @click="toggle()" class="form-select cursor-pointer d-flex justify-content-between align-items-center" :class="{ 'border-primary': open }">
                                <span x-text="selectedLabel || 'Select Currency...'" class="text-truncate"></span>
                                <i class="mdi mdi-chevron-down transition-icon" :class="{ 'rotate-180': open }"></i>
                            </div>

                            <!-- Dropdown Menu -->
                            <div x-show="open" x-cloak x-transition.opacity
                                 class="position-absolute w-100 bg-white border rounded shadow-sm mt-1" 
                                 style="max-height: 250px; overflow-y: auto; z-index: 1050; padding: 8px;">
                                <div class="mb-2 position-sticky top-0 bg-white" style="z-index: 10;">
                                    <input type="text" x-ref="searchInput" x-model="searchQuery" 
                                           @keydown.down.prevent="navigateOptions(1)"
                                           @keydown.up.prevent="navigateOptions(-1)"
                                           @keydown.enter.prevent="selectFocusedOption()"
                                           class="form-control form-control-sm" placeholder="Search..." autocomplete="off">
                                </div>
                                <div class="list-group list-group-flush">
                                    <template x-for="(opt, index) in filteredOptions" :key="opt.value">
                                        <button type="button" @click="selectOption(opt)" 
                                                @mouseenter="focusedIndex = index"
                                                class="list-group-item list-group-item-action border-0 px-2 py-1 rounded"
                                                :class="{'bg-light text-primary fw-medium': focusedIndex === index || selectedValue === opt.value}"
                                                :disabled="opt.disabled">
                                            <span x-text="opt.label" class="font-size-13"></span>
                                        </button>
                                    </template>
                                    <div x-show="filteredOptions.length === 0" class="text-muted text-center py-2 font-size-12">
                                        No results found.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-muted font-size-11">Choose a preset or customize code & symbol below.</span>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label fw-semibold">Default VAT %</label>
                        <div class="input-group">
                            <input type="number" step="0.01" wire:model="default_vat_percent" class="form-control" placeholder="5.00">
                            <span class="input-group-text">%</span>
                        </div>
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
                        <input type="text" wire:model="country" class="form-control">
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
