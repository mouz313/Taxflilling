<!-- Global Toast Component using Alpine.js -->
<div
    x-data="{
        toasts: [],
        add(toast) {
            const id = Date.now() + Math.random();
            this.toasts.push({
                id: id,
                type: toast.type || 'info', // success, error, warning, info
                message: toast.message || '',
                title: toast.title || null,
                timeout: toast.timeout || 4000
            });
            setTimeout(() => {
                this.remove(id);
            }, toast.timeout || 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    @toast.window="add($event.detail)"
    x-init="
        @if(session('success'))
            add({ type: 'success', message: '{{ addslashes(session('success')) }}' });
        @endif
        @if(session('error'))
            add({ type: 'error', message: '{{ addslashes(session('error')) }}' });
        @endif
        @if(session('warning'))
            add({ type: 'warning', message: '{{ addslashes(session('warning')) }}' });
        @endif
        @if(session('info'))
            add({ type: 'info', message: '{{ addslashes(session('info')) }}' });
        @endif
    "
    class="fixed top-4 right-4 z-50 flex flex-col space-y-2 pointer-events-none max-w-sm w-full px-4"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto w-full rounded-xl shadow-lg border p-4 flex items-start space-x-3 transition-all"
            :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-900': toast.type === 'success',
                'bg-rose-50 border-rose-200 text-rose-900': toast.type === 'error',
                'bg-amber-50 border-amber-200 text-amber-900': toast.type === 'warning',
                'bg-sky-50 border-sky-200 text-sky-900': toast.type === 'info'
            }"
        >
            <!-- Icon -->
            <div class="flex-shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </div>

            <!-- Content -->
            <div class="flex-1 text-sm font-medium">
                <template x-if="toast.title">
                    <p class="font-bold mb-0.5" x-text="toast.title"></p>
                </template>
                <p x-text="toast.message" class="leading-relaxed"></p>
            </div>

            <!-- Dismiss Button -->
            <button
                @click="remove(toast.id)"
                class="flex-shrink-0 text-gray-400 hover:text-gray-700 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
