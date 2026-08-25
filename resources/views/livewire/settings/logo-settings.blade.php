<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Logo Management</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form wire:submit.prevent="saveLogos">
                <div class="row g-4">
                    <div class="col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Main Header Logo</label>
                        @if($existing_main_logo)
                            <img src="{{ asset('storage/' . $existing_main_logo) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 80px;">
                        @endif
                        <input type="file" wire:model="main_logo" class="form-control mt-2">
                    </div>

                    <div class="col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Invoice PDF Logo</label>
                        @if($existing_invoice_logo)
                            <img src="{{ asset('storage/' . $existing_invoice_logo) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 80px;">
                        @endif
                        <input type="file" wire:model="invoice_logo" class="form-control mt-2">
                    </div>

                    <div class="col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Report Header Logo</label>
                        @if($existing_report_logo)
                            <img src="{{ asset('storage/' . $existing_report_logo) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 80px;">
                        @endif
                        <input type="file" wire:model="report_logo" class="form-control mt-2">
                    </div>

                    <div class="col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Login Page Logo</label>
                        @if($existing_login_logo)
                            <img src="{{ asset('storage/' . $existing_login_logo) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 80px;">
                        @endif
                        <input type="file" wire:model="login_logo" class="form-control mt-2">
                    </div>

                    <div class="col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Browser Favicon</label>
                        @if($existing_favicon)
                            <img src="{{ asset('storage/' . $existing_favicon) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 40px;">
                        @endif
                        <input type="file" wire:model="favicon" class="form-control mt-2">
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bx bx-upload me-1"></i> Save Logo Uploads
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
