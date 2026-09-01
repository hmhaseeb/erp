<div>
    <!-- Page Header -->
    <x-page-header title="General System Settings" subtitle="Configure numbering prefixes, timezones, date formatting, and inventory rules." />

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form wire:submit.prevent="saveSettings">
                <div class="row">
                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">Date Format</label>
                        <x-searchable-select wire:model="date_format" class="form-select">
                            <option value="Y-m-d">YYYY-MM-DD (2026-08-22)</option>
                            <option value="d/m/Y">DD/MM/YYYY (22/08/2026)</option>
                            <option value="m/d/Y">MM/DD/YYYY (08/22/2026)</option>
                        </x-searchable-select>
                    </div>
                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">Timezone</label>
                        <input type="text" wire:model="time_zone" class="form-control">
                    </div>
                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">Decimal Places</label>
                        <input type="number" wire:model="decimal_places" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">Product SKU Prefix</label>
                        <input type="text" wire:model="product_prefix" class="form-control">
                    </div>
                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">Supplier Code Prefix</label>
                        <input type="text" wire:model="supplier_prefix" class="form-control">
                    </div>
                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">Customer Code Prefix</label>
                        <input type="text" wire:model="customer_prefix" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">Default Cash Account</label>
                        <x-searchable-select wire:model="default_cash_account_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }})</option>
                            @endforeach
                        </x-searchable-select>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">Default Bank Account</label>
                        <x-searchable-select wire:model="default_bank_account_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }})</option>
                            @endforeach
                        </x-searchable-select>
                    </div>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" wire:model="allow_negative_stock" id="negativeStockSwitch">
                    <label class="form-check-label" for="negativeStockSwitch">Allow Negative Inventory Stocking during Sales</label>
                </div>

                <div class="text-sm-end text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4 w-100 w-sm-auto">
                        <i class="bx bx-save me-1"></i> Save General Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
