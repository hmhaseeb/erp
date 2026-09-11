<div>
    @if($showModal)
        <div class="modal fade show d-block erp-modal-backdrop" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.65); z-index: 1060;" wire:keydown.escape="closeWizard">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable erp-modal-dialog">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <!-- Modal Header -->
                    <div class="modal-header border-bottom px-4 py-3 bg-light">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded-circle bg-primary text-white font-size-22 shadow-sm">
                                    <i class="bx bx-rocket"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark mb-0">Initial Business Setup Wizard</h5>
                                <p class="text-muted font-size-12 mb-0">Configure your ERP core settings in 6 simple steps for daily operations.</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeWizard" aria-label="Close"></button>
                    </div>
                    <!-- Modal Body -->
                    <div class="modal-body p-4">
                        <div class="card border border-primary-subtle bg-primary-subtle bg-opacity-10 mb-4 shadow-none">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold text-primary font-size-13">
                                        <i class="bx bx-task me-1"></i> Setup Progress: {{ $completedCount }} of {{ $totalCount }} Completed
                                    </span>
                                    <span class="fw-bold font-monospace text-primary font-size-14">{{ $percentage }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $percentage === 100 ? 'success' : 'primary' }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-3">
                            @foreach($steps as $stepNum => $step)
                                @php $isCurrentNext = (!$step['is_completed'] && $nextStep && $nextStep['step_number'] === $stepNum); @endphp
                                <div class="card border {{ $isCurrentNext ? 'border-primary shadow-sm' : ($step['is_completed'] ? 'border-success-subtle bg-light bg-opacity-50' : 'border-light-subtle') }} mb-0">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center g-3">
                                            <div class="col-auto">
                                                <div class="avatar-sm">
                                                    <span class="avatar-title rounded-circle font-size-18 {{ $step['is_completed'] ? 'bg-success-subtle text-success border border-success-subtle' : ($isCurrentNext ? 'bg-primary text-white shadow-sm' : 'bg-light text-muted border') }}">
                                                        @if($step['is_completed'])<i class="bx bx-check font-size-20 fw-bold"></i>@else{{ $stepNum }}@endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                    <h6 class="mb-0 fw-bold {{ $step['is_completed'] ? 'text-dark' : ($isCurrentNext ? 'text-primary' : 'text-dark') }}">Step {{ $stepNum }}: {{ $step['title'] }}</h6>
                                                    @if($step['is_completed'])
                                                        <span class="badge bg-success-subtle text-success border border-success font-size-11"><i class="bx bx-check-circle me-1"></i> Completed</span>
                                                    @elseif($isCurrentNext)
                                                        <span class="badge bg-primary text-white font-size-11"><i class="bx bx-right-arrow-circle me-1"></i> Recommended Next</span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning border border-warning font-size-11"><i class="bx bx-time-five me-1"></i> Pending</span>
                                                    @endif
                                                </div>
                                                <p class="text-muted font-size-12 mb-0 lh-sm">{{ $step['description'] }}</p>
                                            </div>
                                            <div class="col-12 col-sm-auto text-sm-end">
                                                @if($step['is_completed'])
                                                    <button type="button" wire:click="navigateTo('{{ $step['route'] }}')" class="btn btn-outline-secondary btn-sm w-100 w-sm-auto">
                                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                                    </button>
                                                @else
                                                    <button type="button" wire:click="navigateTo('{{ $step['route'] }}')" class="btn {{ $isCurrentNext ? 'btn-primary' : 'btn-outline-primary' }} btn-sm shadow-sm w-100 w-sm-auto">
                                                        <i class="bx bx-right-arrow-alt me-1"></i> {{ $step['action_label'] }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light px-4 py-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted font-size-11 d-none d-md-inline"><i class="bx bx-info-circle me-1"></i> You can revisit this wizard anytime from the top bar or sidebar settings.</span>
                        <div class="d-flex gap-2 ms-auto w-100 w-md-auto justify-content-end">
                            @if($isComplete)
                                <button type="button" wire:click="closeWizard" class="btn btn-success px-4 w-100 w-sm-auto"><i class="bx bx-check-double me-1"></i> All Set! Go to Dashboard</button>
                            @else
                                <button type="button" wire:click="closeWizard" class="btn btn-light px-3 w-50 w-sm-auto">I'll Do This Later</button>
                                <button type="button" wire:click="continueSetup" class="btn btn-primary px-4 w-50 w-sm-auto"><i class="bx bx-arrow-to-right me-1"></i> Next Step</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
