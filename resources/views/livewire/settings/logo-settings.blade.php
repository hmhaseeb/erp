<div>
    <!-- Page Header -->
    <x-page-header title="Logo Management" subtitle="Manage header branding, PDF report logos, login page graphics, and browser favicons." />

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form wire:submit.prevent="saveLogos">
                <div class="row g-4">
                    <div class="col-12 col-sm-6 col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Main Header Logo</label>
                        @if($existing_main_logo)
                            <img src="{{ asset('storage/' . $existing_main_logo) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 80px;">
                        @endif
                        <input type="file" wire:model="main_logo" class="form-control mt-2">
                    </div>

                    <div class="col-12 col-sm-6 col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Invoice PDF Logo</label>
                        @if($existing_invoice_logo)
                            <img src="{{ asset('storage/' . $existing_invoice_logo) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 80px;">
                        @endif
                        <input type="file" wire:model="invoice_logo" class="form-control mt-2">
                    </div>

                    <div class="col-12 col-sm-6 col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Report Header Logo</label>
                        @if($existing_report_logo)
                            <img src="{{ asset('storage/' . $existing_report_logo) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 80px;">
                        @endif
                        <input type="file" wire:model="report_logo" class="form-control mt-2">
                    </div>

                    <div class="col-12 col-sm-6 col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Login Page Logo</label>
                        @if($existing_login_logo)
                            <img src="{{ asset('storage/' . $existing_login_logo) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 80px;">
                        @endif
                        <input type="file" wire:model="login_logo" class="form-control mt-2">
                    </div>

                    <div class="col-12 col-sm-6 col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Browser Favicon</label>
                        @if($existing_favicon)
                            <img src="{{ asset('storage/' . $existing_favicon) }}" class="img-fluid rounded border p-2 mb-2" style="max-height: 40px;">
                        @endif
                        <input type="file" wire:model="favicon" class="form-control mt-2">
                    </div>

                    <div class="col-12 col-sm-6 col-md-4 text-center">
                        <label class="form-label fw-bold d-block">PWA App Icon (Auto-generated)</label>
                        @if(file_exists(public_path('assets/images/icons/icon-192x192.png')))
                            <img src="{{ asset('assets/images/icons/icon-192x192.png') }}?v={{ time() }}" class="img-fluid rounded-3 border p-1 mb-2 shadow-sm" style="max-height: 60px;">
                        @endif
                        <small class="text-muted d-block font-size-12 mt-1">Synced automatically from Favicon / Logo for PWA installation.</small>
                    </div>
                </div>

                <div class="text-sm-end text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4 w-100 w-sm-auto">
                        <i class="bx bx-upload me-1"></i> Save Logo Uploads
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
