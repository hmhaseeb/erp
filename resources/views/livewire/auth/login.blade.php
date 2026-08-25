@php
    $companySetting = \App\Services\SettingsService::getCompany();
@endphp
<div class="row g-0 justify-content-center align-items-center min-vh-100 bg-light">
    <div class="col-xxl-3 col-lg-4 col-md-6">
        <div class="card border-0 shadow-lg">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-4">
                    @if($companySetting && ($companySetting->login_logo || $companySetting->main_logo))
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . ($companySetting->login_logo ?: $companySetting->main_logo)) }}" alt="Logo" style="max-height: 50px; max-width: 220px; object-fit: contain;">
                        </div>
                    @else
                        <div class="avatar-md mx-auto mb-3">
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-24">
                                <i class="bx bx-store-alt"></i>
                            </div>
                        </div>
                    @endif
                    <h5 class="mb-0 text-dark fw-bold">{{ $companySetting->company_name ?? 'Small Business ERP' }}</h5>
                    <p class="text-muted font-size-13 mt-1">Single Administrator Login</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger font-size-13">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form wire:submit.prevent="login" class="mt-4">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" wire:model="email" class="form-control" placeholder="Enter admin email">
                        @error('email') <span class="text-danger font-size-12">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" wire:model="password" class="form-control" placeholder="Enter password">
                        @error('password') <span class="text-danger font-size-12">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" wire:model="remember" id="remember-check">
                        <label class="form-check-label" for="remember-check">
                            Remember me
                        </label>
                    </div>

                    <div class="mb-3 d-grid">
                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                            <span wire:loading.remove>Log In</span>
                            <span wire:loading><i class="bx bx-loader-alt bx-spin align-middle me-1"></i> Authenticating...</span>
                        </button>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <p class="text-muted font-size-12 mb-0">Default Admin: <strong>admin@erp.com</strong> / <strong>admin123</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
