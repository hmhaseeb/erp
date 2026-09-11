<div>
    <!-- Page Header -->
    <x-page-header title="My Profile & Security" subtitle="Manage your personal account details, login email, and system security password." />

    <div class="row g-4">
        <!-- Left Column: User Summary & Security Info -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 text-center p-3 mb-4">
                <div class="card-body">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-32 shadow-sm">
                            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $user->name ?? 'Administrator' }}</h5>
                    <p class="text-muted font-size-13 mb-2">{{ $user->email ?? 'admin@erp.com' }}</p>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-size-12 px-3 py-1 mb-3">
                        <i class="bx bx-shield-quarter me-1"></i> System Administrator
                    </span>

                    <div class="border-top pt-3 text-start font-size-12 text-muted">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bx bx-calendar me-1"></i> Member Since:</span>
                            <span class="fw-semibold text-dark">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bx bx-check-shield me-1"></i> Account Status:</span>
                            <span class="badge bg-success-subtle text-success border border-success font-size-11">Active</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="bx bx-key me-1"></i> Security:</span>
                            <span class="fw-semibold text-dark">Password Protected</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 bg-light bg-opacity-50">
                <div class="card-body p-3">
                    <h6 class="fw-bold font-size-12 text-uppercase text-muted mb-2">
                        <i class="bx bx-lock-alt text-warning me-1"></i> Password Security Tips
                    </h6>
                    <ul class="font-size-12 text-muted ps-3 mb-0 lh-base">
                        <li>Use at least 8 characters with a mix of letters, numbers, and symbols.</li>
                        <li>Avoid using easily guessable personal dates or phone numbers.</li>
                        <li>Change your password periodically to keep your ERP system secure.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Column: Profile Form & Password Form -->
        <div class="col-12 col-lg-8">
            <!-- Form 1: Edit Profile Information -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-user text-primary"></i> Personal Profile Information
                    </h5>
                    <p class="text-muted font-size-12 mb-0">Update your account name and administrative email address.</p>
                </div>
                <div class="card-body p-4">
                    @if(session()->has('profile_success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
                            <i class="bx bx-check-circle me-1"></i> {{ session('profile_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form wire:submit.prevent="updateProfile">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold font-size-13">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bx bx-user"></i></span>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Your full name">
                                </div>
                                @error('name') <div class="text-danger font-size-12 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold font-size-13">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bx bx-envelope"></i></span>
                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="admin@erp.com">
                                </div>
                                @error('email') <div class="text-danger font-size-12 mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-semibold">
                                <i class="bx bx-save me-1"></i> Save Profile Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Form 2: Change Password -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-shield-quarter text-warning"></i> Change Password
                    </h5>
                    <p class="text-muted font-size-12 mb-0">Ensure your account is using a long, secure password.</p>
                </div>
                <div class="card-body p-4">
                    @if(session()->has('password_success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
                            <i class="bx bx-check-circle me-1"></i> {{ session('password_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form wire:submit.prevent="updatePassword">
                        <!-- Current Password -->
                        <div class="mb-3" x-data="{ show: false }">
                            <label class="form-label fw-bold font-size-13">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bx bx-lock"></i></span>
                                <input :type="show ? 'text' : 'password'" wire:model="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password">
                                <button type="button" class="btn btn-outline-secondary" @click="show = !show" tabindex="-1">
                                    <i class="bx" :class="show ? 'bx-hide' : 'bx-show'"></i>
                                </button>
                            </div>
                            @error('current_password') <div class="text-danger font-size-12 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- New Password -->
                            <div class="col-12 col-md-6" x-data="{ show: false }">
                                <label class="form-label fw-bold font-size-13">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bx bx-key"></i></span>
                                    <input :type="show ? 'text' : 'password'" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimum 8 characters">
                                    <button type="button" class="btn btn-outline-secondary" @click="show = !show" tabindex="-1">
                                        <i class="bx" :class="show ? 'bx-hide' : 'bx-show'"></i>
                                    </button>
                                </div>
                                @error('password') <div class="text-danger font-size-12 mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-12 col-md-6" x-data="{ show: false }">
                                <label class="form-label fw-bold font-size-13">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bx bx-check-double"></i></span>
                                    <input :type="show ? 'text' : 'password'" wire:model="password_confirmation" class="form-control" placeholder="Re-type new password">
                                    <button type="button" class="btn btn-outline-secondary" @click="show = !show" tabindex="-1">
                                        <i class="bx" :class="show ? 'bx-hide' : 'bx-show'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-warning px-4 shadow-sm fw-semibold text-dark">
                                <i class="bx bx-lock-open me-1"></i> Update Security Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
