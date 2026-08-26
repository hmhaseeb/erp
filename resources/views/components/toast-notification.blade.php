<div x-data="toastNotificationManager()"
     x-init="initToasts()"
     class="toast-container position-fixed top-0 end-0 p-3"
     style="z-index: 10900; pointer-events: none;"
     aria-live="polite"
     aria-atomic="true">

    <template x-for="toast in toasts" :key="toast.id">
        <div class="toast show mb-2 border-0 shadow-lg"
             :class="{
                'border-start border-4 border-success': toast.type === 'success',
                'border-start border-4 border-danger': toast.type === 'danger' || toast.type === 'error',
                'border-start border-4 border-warning': toast.type === 'warning',
                'border-start border-4 border-info': toast.type === 'info'
             }"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="pointer-events: auto; background-color: #ffffff; min-width: 300px; max-width: 420px; transition: all 0.3s ease;">
            <div class="toast-header bg-transparent border-0 pb-0 pt-2 px-3 d-flex align-items-center">
                <i class="font-size-18 me-2"
                   :class="{
                       'bx bx-check-circle text-success': toast.type === 'success',
                       'bx bx-error-circle text-danger': toast.type === 'danger' || toast.type === 'error',
                       'bx bx-error text-warning': toast.type === 'warning',
                       'bx bx-info-circle text-info': toast.type === 'info'
                   }"></i>
                <strong class="me-auto font-size-13 text-dark" x-text="toast.title || getDefaultTitle(toast.type)"></strong>
                <small class="text-muted font-size-11">just now</small>
                <button type="button" class="btn-close ms-2" @click="removeToast(toast.id)" aria-label="Close"></button>
            </div>
            <div class="toast-body pt-1 pb-2 px-3 font-size-13 text-secondary" x-text="toast.message">
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('toastNotificationManager', () => ({
            toasts: [],
            nextId: 1,

            initToasts() {
                // Read existing Laravel session flash messages on render
                @if (session()->has('success'))
                    this.addToast(@json(session('success')), 'success', 'Success');
                @endif
                @if (session()->has('error'))
                    this.addToast(@json(session('error')), 'danger', 'Error');
                @endif
                @if (session()->has('warning'))
                    this.addToast(@json(session('warning')), 'warning', 'Warning');
                @endif
                @if (session()->has('info'))
                    this.addToast(@json(session('info')), 'info', 'Information');
                @endif

                // Listen for native custom events
                window.addEventListener('toast', (e) => {
                    const data = e.detail || {};
                    const payload = Array.isArray(data) ? data[0] : data;
                    const message = typeof payload === 'string' ? payload : (payload.message || '');
                    const type = payload.type || 'info';
                    const title = payload.title || '';
                    if (message) {
                        this.addToast(message, type, title);
                    }
                });

                // Listen for Livewire 3 event dispatches
                if (typeof Livewire !== 'undefined') {
                    Livewire.on('toast', (data) => {
                        const payload = Array.isArray(data) ? data[0] : data;
                        const message = typeof payload === 'string' ? payload : (payload.message || '');
                        const type = payload.type || 'info';
                        const title = payload.title || '';
                        if (message) {
                            this.addToast(message, type, title);
                        }
                    });
                } else {
                    document.addEventListener('livewire:init', () => {
                        Livewire.on('toast', (data) => {
                            const payload = Array.isArray(data) ? data[0] : data;
                            const message = typeof payload === 'string' ? payload : (payload.message || '');
                            const type = payload.type || 'info';
                            const title = payload.title || '';
                            if (message) {
                                this.addToast(message, type, title);
                            }
                        });
                    });
                }

                // Global helper function
                window.showToast = (message, type = 'info', title = '') => {
                    this.addToast(message, type, title);
                };
            },

            addToast(message, type = 'info', title = '') {
                // Avoid exact duplicates triggered within 1000ms
                const existing = this.toasts.find(t => t.message === message && (Date.now() - t.timestamp < 1000));
                if (existing) return;

                const id = this.nextId++;
                const toastObj = {
                    id: id,
                    message: message,
                    type: type,
                    title: title,
                    timestamp: Date.now()
                };

                this.toasts.push(toastObj);

                // Auto dismiss after 4 seconds
                setTimeout(() => {
                    this.removeToast(id);
                }, 4000);
            },

            removeToast(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            },

            getDefaultTitle(type) {
                if (type === 'success') return 'Success';
                if (type === 'danger' || type === 'error') return 'Error';
                if (type === 'warning') return 'Warning';
                return 'Notification';
            }
        }));
    });
</script>
