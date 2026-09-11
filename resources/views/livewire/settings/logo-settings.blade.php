<div>
    <!-- Page Header -->
    <x-page-header title="Logo & Brand Identity" subtitle="Manage header branding, PDF report logos, login page graphics, and browser favicons." />

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

    <!-- Official Default SmallBiz Brand Showcase Banner -->
    <div class="card border border-primary-subtle shadow-sm mb-4 rounded-3 overflow-hidden">
        <div class="card-header bg-primary bg-opacity-10 py-3 px-4 border-bottom border-primary-subtle d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <span class="avatar-xs rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center">
                        <i class="bx bx-shield-quarter font-size-16"></i>
                    </span>
                    Official SmallBiz ERP Default Branding
                </h5>
                <p class="text-muted font-size-12 mb-0">High-resolution brand assets pre-configured for your ERP. You can optionally apply them in 1-click or select individually below.</p>
            </div>
            <div>
                <button type="button" wire:click="applyDefaultLogos" class="btn btn-primary px-3 py-2 shadow-sm font-size-13 fw-semibold">
                    <i class="bx bx-magic-wand me-1"></i> Apply All Default SmallBiz Logos
                </button>
            </div>
        </div>
        <div class="card-body p-4 bg-light bg-opacity-25">
            <div class="row g-3">
                <!-- SmallBiz Full Logo Preview -->
                <div class="col-12 col-md-6">
                    <div class="card border border-light-subtle h-100 shadow-none mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded border p-2 bg-white d-flex align-items-center justify-content-center" style="height: 60px; min-width: 140px; max-width: 160px;">
                                    <img src="{{ asset('assets/images/branding/smallbiz-logo.png') }}" alt="SmallBiz Logo" class="img-fluid" style="max-height: 46px; object-fit: contain;">
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">SmallBiz Full Logo</h6>
                                    <span class="text-muted font-size-12 d-block">Horizontal brand mark with ribbon emblem & title</span>
                                </div>
                            </div>
                            <div class="font-size-11 text-muted mb-2 fw-semibold text-uppercase">Assigned to:</div>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bx bx-check me-1"></i> Main Header Logo</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bx bx-check me-1"></i> Invoice PDF Logo</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bx bx-check me-1"></i> Report Header Logo</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bx bx-check me-1"></i> Login Page Logo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SmallBiz Icon Preview -->
                <div class="col-12 col-md-6">
                    <div class="card border border-light-subtle h-100 shadow-none mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded border p-2 bg-white d-flex align-items-center justify-content-center" style="height: 60px; width: 60px; min-width: 60px;">
                                    <img src="{{ asset('assets/images/branding/smallbiz-icon.png') }}" alt="SmallBiz Icon" class="img-fluid" style="max-height: 46px; object-fit: contain;">
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">SmallBiz Icon</h6>
                                    <span class="text-muted font-size-12 d-block">Square ribbon 3D symbol for compact spaces</span>
                                </div>
                            </div>
                            <div class="font-size-11 text-muted mb-2 fw-semibold text-uppercase">Assigned to:</div>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bx bx-check me-1"></i> Browser Favicon</span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bx bx-check me-1"></i> PWA / WPA App Icon</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form for Custom Uploads and Slot-by-Slot Select -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title fw-bold mb-0">Branding Slot Configuration</h5>
                <p class="text-muted font-size-12 mb-0">Choose the default SmallBiz logo or upload your custom logo for each slot.</p>
            </div>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="saveLogos">
                <div class="row g-4">
                    <!-- 1. Main Header Logo -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border h-100 rounded-3 shadow-none mb-0">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 font-size-13">
                                        <i class="bx bx-window-alt text-primary me-1"></i> Main Header Logo
                                    </label>
                                    @if($existing_main_logo === \App\Livewire\Settings\LogoSettings::DEFAULT_LOGO_PATH)
                                        <span class="badge bg-success-subtle text-success border border-success font-size-11">Default SmallBiz</span>
                                    @elseif($existing_main_logo)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle font-size-11">Custom Logo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary font-size-11">Not Set</span>
                                    @endif
                                </div>
                                <p class="text-muted font-size-11 mb-2">Displayed in top navigation bar across all ERP pages.</p>
                                
                                <div class="rounded border p-2 mb-3 d-flex align-items-center justify-content-center bg-white mx-auto w-100" style="height: 85px;">
                                    @if($existing_main_logo)
                                        <img src="{{ asset('storage/' . $existing_main_logo) }}" class="img-fluid" style="max-height: 65px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('assets/images/branding/smallbiz-logo.png') }}" class="img-fluid opacity-50" style="max-height: 65px; object-fit: contain;">
                                    @endif
                                </div>

                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" wire:click="useDefault('main_logo')" class="btn btn-sm btn-outline-primary w-100 font-size-12">
                                        <i class="bx bx-check me-1"></i> Select Default
                                    </button>
                                    @if($existing_main_logo)
                                        <button type="button" wire:click="removeLogo('main_logo')" class="btn btn-sm btn-outline-danger font-size-12" title="Clear">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-auto">
                                    <label class="form-label font-size-11 text-muted mb-1">Or upload custom file:</label>
                                    <input type="file" wire:model="main_logo" class="form-control form-control-sm">
                                    <div wire:loading wire:target="main_logo" class="text-primary font-size-11 mt-1"><i class="bx bx-loader-alt bx-spin"></i> Uploading...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Invoice PDF Logo -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border h-100 rounded-3 shadow-none mb-0">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 font-size-13">
                                        <i class="bx bx-receipt text-primary me-1"></i> Invoice PDF Logo
                                    </label>
                                    @if($existing_invoice_logo === \App\Livewire\Settings\LogoSettings::DEFAULT_LOGO_PATH)
                                        <span class="badge bg-success-subtle text-success border border-success font-size-11">Default SmallBiz</span>
                                    @elseif($existing_invoice_logo)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle font-size-11">Custom Logo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary font-size-11">Not Set</span>
                                    @endif
                                </div>
                                <p class="text-muted font-size-11 mb-2">Printed at header of sales & purchase PDF invoices.</p>
                                
                                <div class="rounded border p-2 mb-3 d-flex align-items-center justify-content-center bg-white mx-auto w-100" style="height: 85px;">
                                    @if($existing_invoice_logo)
                                        <img src="{{ asset('storage/' . $existing_invoice_logo) }}" class="img-fluid" style="max-height: 65px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('assets/images/branding/smallbiz-logo.png') }}" class="img-fluid opacity-50" style="max-height: 65px; object-fit: contain;">
                                    @endif
                                </div>

                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" wire:click="useDefault('invoice_logo')" class="btn btn-sm btn-outline-primary w-100 font-size-12">
                                        <i class="bx bx-check me-1"></i> Select Default
                                    </button>
                                    @if($existing_invoice_logo)
                                        <button type="button" wire:click="removeLogo('invoice_logo')" class="btn btn-sm btn-outline-danger font-size-12" title="Clear">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-auto">
                                    <label class="form-label font-size-11 text-muted mb-1">Or upload custom file:</label>
                                    <input type="file" wire:model="invoice_logo" class="form-control form-control-sm">
                                    <div wire:loading wire:target="invoice_logo" class="text-primary font-size-11 mt-1"><i class="bx bx-loader-alt bx-spin"></i> Uploading...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Report Header Logo -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border h-100 rounded-3 shadow-none mb-0">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 font-size-13">
                                        <i class="bx bx-bar-chart-alt-2 text-primary me-1"></i> Report Header Logo
                                    </label>
                                    @if($existing_report_logo === \App\Livewire\Settings\LogoSettings::DEFAULT_LOGO_PATH)
                                        <span class="badge bg-success-subtle text-success border border-success font-size-11">Default SmallBiz</span>
                                    @elseif($existing_report_logo)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle font-size-11">Custom Logo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary font-size-11">Not Set</span>
                                    @endif
                                </div>
                                <p class="text-muted font-size-11 mb-2">Rendered on printable financial and stock statements.</p>
                                
                                <div class="rounded border p-2 mb-3 d-flex align-items-center justify-content-center bg-white mx-auto w-100" style="height: 85px;">
                                    @if($existing_report_logo)
                                        <img src="{{ asset('storage/' . $existing_report_logo) }}" class="img-fluid" style="max-height: 65px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('assets/images/branding/smallbiz-logo.png') }}" class="img-fluid opacity-50" style="max-height: 65px; object-fit: contain;">
                                    @endif
                                </div>

                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" wire:click="useDefault('report_logo')" class="btn btn-sm btn-outline-primary w-100 font-size-12">
                                        <i class="bx bx-check me-1"></i> Select Default
                                    </button>
                                    @if($existing_report_logo)
                                        <button type="button" wire:click="removeLogo('report_logo')" class="btn btn-sm btn-outline-danger font-size-12" title="Clear">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-auto">
                                    <label class="form-label font-size-11 text-muted mb-1">Or upload custom file:</label>
                                    <input type="file" wire:model="report_logo" class="form-control form-control-sm">
                                    <div wire:loading wire:target="report_logo" class="text-primary font-size-11 mt-1"><i class="bx bx-loader-alt bx-spin"></i> Uploading...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Login Page Logo -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border h-100 rounded-3 shadow-none mb-0">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 font-size-13">
                                        <i class="bx bx-log-in-circle text-primary me-1"></i> Login Page Logo
                                    </label>
                                    @if($existing_login_logo === \App\Livewire\Settings\LogoSettings::DEFAULT_LOGO_PATH)
                                        <span class="badge bg-success-subtle text-success border border-success font-size-11">Default SmallBiz</span>
                                    @elseif($existing_login_logo)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle font-size-11">Custom Logo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary font-size-11">Not Set</span>
                                    @endif
                                </div>
                                <p class="text-muted font-size-11 mb-2">Shown above login card on the authentication screen.</p>
                                
                                <div class="rounded border p-2 mb-3 d-flex align-items-center justify-content-center bg-white mx-auto w-100" style="height: 85px;">
                                    @if($existing_login_logo)
                                        <img src="{{ asset('storage/' . $existing_login_logo) }}" class="img-fluid" style="max-height: 65px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('assets/images/branding/smallbiz-logo.png') }}" class="img-fluid opacity-50" style="max-height: 65px; object-fit: contain;">
                                    @endif
                                </div>

                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" wire:click="useDefault('login_logo')" class="btn btn-sm btn-outline-primary w-100 font-size-12">
                                        <i class="bx bx-check me-1"></i> Select Default
                                    </button>
                                    @if($existing_login_logo)
                                        <button type="button" wire:click="removeLogo('login_logo')" class="btn btn-sm btn-outline-danger font-size-12" title="Clear">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-auto">
                                    <label class="form-label font-size-11 text-muted mb-1">Or upload custom file:</label>
                                    <input type="file" wire:model="login_logo" class="form-control form-control-sm">
                                    <div wire:loading wire:target="login_logo" class="text-primary font-size-11 mt-1"><i class="bx bx-loader-alt bx-spin"></i> Uploading...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Browser Favicon -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border h-100 rounded-3 shadow-none mb-0">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 font-size-13">
                                        <i class="bx bx-globe text-primary me-1"></i> Browser Favicon
                                    </label>
                                    @if($existing_favicon === \App\Livewire\Settings\LogoSettings::DEFAULT_ICON_PATH)
                                        <span class="badge bg-success-subtle text-success border border-success font-size-11">Default SmallBiz</span>
                                    @elseif($existing_favicon)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle font-size-11">Custom Favicon</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary font-size-11">Not Set</span>
                                    @endif
                                </div>
                                <p class="text-muted font-size-11 mb-2">Tab icon and browser shortcut bookmark icon.</p>
                                
                                <div class="rounded border p-2 mb-3 d-flex align-items-center justify-content-center bg-white mx-auto w-100" style="height: 85px;">
                                    @if($existing_favicon)
                                        <img src="{{ asset('storage/' . $existing_favicon) }}" class="img-fluid" style="max-height: 48px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('assets/images/branding/smallbiz-icon.png') }}" class="img-fluid opacity-50" style="max-height: 48px; object-fit: contain;">
                                    @endif
                                </div>

                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" wire:click="useDefault('favicon')" class="btn btn-sm btn-outline-primary w-100 font-size-12">
                                        <i class="bx bx-check me-1"></i> Select Default Icon
                                    </button>
                                    @if($existing_favicon)
                                        <button type="button" wire:click="removeLogo('favicon')" class="btn btn-sm btn-outline-danger font-size-12" title="Clear">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-auto">
                                    <label class="form-label font-size-11 text-muted mb-1">Or upload custom icon:</label>
                                    <input type="file" wire:model="favicon" class="form-control form-control-sm">
                                    <div wire:loading wire:target="favicon" class="text-primary font-size-11 mt-1"><i class="bx bx-loader-alt bx-spin"></i> Uploading...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. PWA / WPA App Icons (Auto-Generated) -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border h-100 rounded-3 shadow-none mb-0 bg-light bg-opacity-25">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 font-size-13">
                                        <i class="bx bx-mobile-alt text-info me-1"></i> PWA / WPA App Icon
                                    </label>
                                    <span class="badge bg-success-subtle text-success border border-success font-size-11">Auto-Synced</span>
                                </div>
                                <p class="text-muted font-size-11 mb-2">Installed mobile app icon & desktop splash icon (72px to 512px).</p>
                                
                                <div class="rounded border p-2 mb-3 d-flex align-items-center justify-content-center gap-3 bg-white mx-auto w-100" style="height: 85px;">
                                    @if(file_exists(public_path('assets/images/icons/icon-192x192.png')))
                                        <img src="{{ asset('assets/images/icons/icon-192x192.png') }}?v={{ time() }}" class="rounded shadow-sm border p-1" style="height: 52px; width: 52px; object-fit: contain;" title="192x192 App Icon">
                                        <img src="{{ asset('assets/images/icons/apple-touch-icon.png') }}?v={{ time() }}" class="rounded shadow-sm border p-1" style="height: 52px; width: 52px; object-fit: contain;" title="Apple Touch Icon">
                                    @else
                                        <img src="{{ asset('assets/images/branding/smallbiz-icon.png') }}" class="rounded shadow-sm border p-1" style="height: 52px; width: 52px; object-fit: contain;">
                                    @endif
                                </div>

                                <div class="mt-auto">
                                    <button type="button" wire:click="generatePwaIcons" class="btn btn-sm btn-outline-secondary w-100 font-size-12">
                                        <i class="bx bx-sync me-1"></i> Regenerate PWA Icons
                                    </button>
                                    <small class="text-muted d-block font-size-11 mt-1 text-center">Synced with SmallBiz Icon automatically.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
                    <span class="text-muted font-size-12">
                        <i class="bx bx-info-circle me-1"></i> Uploading a custom file overrides the default for that slot. Click Save below to store new uploads.
                    </span>
                    <button type="submit" class="btn btn-success px-4 shadow-sm fw-semibold">
                        <i class="bx bx-save me-1"></i> Save Custom Logo Uploads
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
