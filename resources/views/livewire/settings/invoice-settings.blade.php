<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Invoice & Numbering Settings</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form wire:submit.prevent="saveSettings">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sales Invoice Prefix</label>
                        <input type="text" wire:model="invoice_prefix" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Purchase Invoice Prefix</label>
                        <input type="text" wire:model="purchase_prefix" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sales Return Prefix</label>
                        <input type="text" wire:model="sales_return_prefix" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Purchase Return Prefix</label>
                        <input type="text" wire:model="purchase_return_prefix" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Customer Receipt Prefix</label>
                        <input type="text" wire:model="customer_payment_prefix" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Supplier Payment Voucher Prefix</label>
                        <input type="text" wire:model="supplier_payment_prefix" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Terms & Conditions (Printed on PDF)</label>
                        <textarea wire:model="terms_conditions" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank Payment Details (Printed on PDF)</label>
                        <textarea wire:model="bank_details" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Invoice Footer Note</label>
                    <input type="text" wire:model="invoice_footer" class="form-control">
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bx bx-save me-1"></i> Save Invoice Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
