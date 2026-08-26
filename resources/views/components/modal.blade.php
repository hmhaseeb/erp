@props([
    'isOpen' => false,
    'title' => '',
    'size' => 'modal-dialog-centered',
    'submitAction' => null,
    'isEditMode' => false,
    'saveText' => 'Save',
    'updateText' => 'Update',
    'closeAction' => 'closeModal',
    'savingText' => 'Saving...',
    'theme' => 'primary',
    'footer' => null,
])

@if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog {{ $size }}">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $title }}</h5>
                    <button type="button" class="btn-close" wire:click="{{ $closeAction }}" aria-label="Close"></button>
                </div>
                @if($submitAction)
                    <form wire:submit.prevent="{{ $submitAction }}">
                        <div class="modal-body">
                            @if(session()->has('modal_error'))
                                <div class="alert alert-danger alert-dismissible fade show font-size-13 py-2 px-3 mb-3" role="alert">
                                    <i class="bx bx-error-circle me-1"></i> {{ session('modal_error') }}
                                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if($errors->has('payment_error'))
                                <div class="alert alert-danger font-size-13 py-2 px-3 mb-3" role="alert">
                                    <i class="bx bx-error-circle me-1"></i> {{ $errors->first('payment_error') }}
                                </div>
                            @endif

                            {{ $slot }}
                        </div>
                        <div class="modal-footer">
                            @if(isset($footer))
                                {{ $footer }}
                            @else
                                <button type="button" class="btn btn-light" wire:click="{{ $closeAction }}">Cancel</button>
                                <button type="submit" class="btn btn-{{ $theme }}">
                                    <span wire:loading.remove wire:target="{{ $submitAction }}">
                                        {{ $isEditMode ? $updateText : $saveText }}
                                    </span>
                                    <span wire:loading wire:target="{{ $submitAction }}">
                                        <i class="bx bx-loader-alt bx-spin me-1"></i> {{ $savingText }}
                                    </span>
                                </button>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="modal-body">
                        @if(session()->has('modal_error'))
                            <div class="alert alert-danger alert-dismissible fade show font-size-13 py-2 px-3 mb-3" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('modal_error') }}
                                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                    @if(isset($footer))
                        <div class="modal-footer">
                            {{ $footer }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endif
