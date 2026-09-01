@props([
    'isOpen' => false,
    'title' => '',
    'size' => 'modal-dialog-centered modal-dialog-scrollable',
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
    @php
        $modalClasses = 'modal-dialog erp-modal-dialog';
        if (!str_contains($size, 'modal-dialog-scrollable')) {
            $modalClasses .= ' modal-dialog-scrollable';
        }
        if (!str_contains($size, 'modal-dialog-centered') && !str_contains($size, 'modal-dialog-top')) {
            $modalClasses .= ' modal-dialog-centered';
        }
        $modalClasses .= ' ' . $size;
    @endphp
    <div x-data="{
            closing: false,
            close() {
                if (this.closing) return;
                this.closing = true;
                $wire.{{ $closeAction }}();
            }
         }"
         @keydown.escape.window.stop="close()"
         @click.self.stop="close()"
         class="modal fade show d-block erp-modal-backdrop" 
         style="background: rgba(0,0,0,0.5); z-index: 1055;" 
         tabindex="-1" 
         role="dialog" 
         aria-modal="true">
        <div class="{{ trim($modalClasses) }}" @click.stop>
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title font-size-15 fw-bold">{{ $title }}</h5>
                    <button type="button" class="btn-close" @click.stop="close()" aria-label="Close"></button>
                </div>
                @if($submitAction)
                    <form wire:submit.prevent="{{ $submitAction }}" class="d-flex flex-column h-100 overflow-hidden flex-fill min-h-0">
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
                        <div class="modal-footer d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                            @if(isset($footer))
                                {{ $footer }}
                            @else
                                <button type="button" class="btn btn-light" @click.stop="close()" wire:loading.attr="disabled" wire:target="{{ $submitAction }}">Cancel</button>
                                <button type="submit" class="btn btn-{{ $theme }}" wire:loading.attr="disabled" wire:target="{{ $submitAction }}">
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
                    <div class="modal-footer d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                        @if(isset($footer))
                            {{ $footer }}
                        @else
                            <button type="button" class="btn btn-light" @click.stop="close()">Close</button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
