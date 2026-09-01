@php
    $companySetting = \App\Services\SettingsService::getCompany();
@endphp
<div class="row g-0 justify-content-center align-items-center min-vh-100 bg-light-subtle py-4">
    <div class="col-xxl-3 col-lg-4 col-md-6 col-sm-10 col-12 px-3">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-4">
                    @if($companySetting && ($companySetting->login_logo ?: $companySetting->main_logo))
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . ($companySetting->login_logo ?: $companySetting->main_logo)) }}" alt="Logo" style="max-height: 55px; max-width: 220px; object-fit: contain;">
                        </div>
                    @else
                        <div class="avatar-md mx-auto mb-3">
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-28">
                                <i class="bx bx-store-alt"></i>
                            </div>
                        </div>
                    @endif
                    <h4 class="mb-1 text-dark fw-bold">{{ $companySetting->company_name ?? 'Small Business ERP' }}</h4>
                    <p class="text-muted font-size-13 mb-0">Sign in to access your ERP portal</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show font-size-13 rounded-3" role="alert">
                        <i class="bx bx-error-circle me-1 font-size-15 align-middle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form wire:submit.prevent="login" class="mt-4">
                    <div class="mb-3">
                        <label class="form-label font-size-13 fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bx bx-envelope"></i></span>
                            <input type="email" wire:model="email" class="form-control border-start-0" placeholder="Enter your email" required autofocus>
                        </div>
                        @error('email') <span class="text-danger font-size-12 mt-1 d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3" x-data="{ showPassword: false }">
                        <label class="form-label font-size-13 fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bx bx-lock-alt"></i></span>
                            <input :type="showPassword ? 'text' : 'password'" wire:model="password" class="form-control border-start-0 border-end-0" placeholder="Enter your password" required>
                            <button class="btn btn-light border border-start-0 text-muted px-3" type="button" @click="showPassword = !showPassword" title="Toggle password visibility" tabindex="-1">
                                <i class="bx" :class="showPassword ? 'bx-hide' : 'bx-show'"></i>
                            </button>
                        </div>
                        @error('password') <span class="text-danger font-size-12 mt-1 d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="remember" id="remember-check">
                            <label class="form-check-label font-size-13 text-muted" for="remember-check">
                                Keep me signed in
                            </label>
                        </div>
                    </div>

                    <div class="mb-3 d-grid">
                        <button class="btn btn-primary btn-lg font-size-14 fw-semibold waves-effect waves-light shadow-sm py-2" type="submit">
                            <span wire:loading.remove><i class="bx bx-log-in me-1"></i> Sign In</span>
                            <span wire:loading><i class="bx bx-loader-alt bx-spin align-middle me-1"></i> Authenticating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-4 text-center">
            <p class="text-muted font-size-12 mb-0">&copy; {{ date('Y') }} {{ $companySetting->company_name ?? 'Small Business ERP' }}. All rights reserved.</p>
        </div>
    </div>
</div>
