@props(['type' => 'success', 'message' => ''])

<div x-data="notificationManager()" x-init="init('{{ $type }}', '{{ $message }}')" x-show="show" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0" class="fixed top-4 right-4 z-50 max-w-sm w-full" style="display: none;">
    <div class="rounded-lg shadow-lg p-4 flex items-start gap-3" :class="{
        'bg-green-50 border border-green-200': type === 'success',
        'bg-red-50 border border-red-200': type === 'error',
        'bg-blue-50 border border-blue-200': type === 'info',
        'bg-yellow-50 border border-yellow-200': type === 'warning'
    }">
        <div class="flex-shrink-0">
            <template x-if="type === 'success'">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </template>
            <template x-if="type === 'error'">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </template>
            <template x-if="type === 'info'">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </template>
            <template x-if="type === 'warning'">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </template>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium" :class="{
                'text-green-800': type === 'success',
                'text-red-800': type === 'error',
                'text-blue-800': type === 'info',
                'text-yellow-800': type === 'warning'
            }" x-text="message"></p>
        </div>
        <button @click="close()" class="flex-shrink-0 ml-2" :class="{
            'text-green-500 hover:text-green-700': type === 'success',
            'text-red-500 hover:text-red-700': type === 'error',
            'text-blue-500 hover:text-blue-700': type === 'info',
            'text-yellow-500 hover:text-yellow-700': type === 'warning'
        }">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<script>
    function notificationManager() {
        return {
            show: false,
            type: 'success',
            message: '',
            timeout: null,
            init(initialType, initialMessage) {
                if (initialMessage) {
                    this.type = initialType;
                    this.message = initialMessage;
                    this.show = true;
                    this.autoClose();
                }
            },
            display(type, message) {
                this.type = type;
                this.message = message;
                this.show = true;
                this.autoClose();
            },
            autoClose() {
                if (this.timeout) {
                    clearTimeout(this.timeout);
                }
                this.timeout = setTimeout(() => {
                    this.close();
                }, 5000);
            },
            close() {
                this.show = false;
                if (this.timeout) {
                    clearTimeout(this.timeout);
                }
            }
        }
    }
</script>
