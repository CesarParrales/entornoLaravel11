<div
    x-data="{
        show: @entangle('show'),
        type: @entangle('type'),
        message: @entangle('message'),
        timer: null,
        autoHide() {
            clearTimeout(this.timer);
            if (this.show) {
                this.timer = setTimeout(() => {
                    $wire.hideNotification();
                }, 5000);
            }
        }
    }"
    x-show="show"
    x-init="$watch('show', value => autoHide())"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-300 transform"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed bottom-0 right-0 z-[100] p-4 m-4 md:m-6 max-w-sm w-full rounded-lg shadow-2xl text-white"
    :class="{
        'bg-green-600': type === 'success',
        'bg-red-600': type === 'error',
        'bg-yellow-500 text-gray-800': type === 'warning',
        'bg-blue-600': type === 'info',
        'hidden': !show
    }"
    role="alert"
    style="display: none;" {{-- Evitar FOUC, Alpine lo manejará --}}
    aria-live="assertive"
    aria-atomic="true"
>
    <div class="flex items-start justify-between">
        <div class="flex items-center">
            <!-- Iconos opcionales según el tipo -->
            <span x-show="type === 'success'">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </span>
            <span x-show="type === 'error'">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </span>
            <span x-show="type === 'info'">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </span>
            <span x-show="type === 'warning'">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </span>
            <span x-text="message" class="text-sm font-medium"></span>
        </div>
        <button @click="show = false" class="ml-3 -mr-1 -mt-1 text-xl font-semibold leading-none hover:opacity-75 focus:outline-none">
            &times;
        </button>
    </div>
</div>
