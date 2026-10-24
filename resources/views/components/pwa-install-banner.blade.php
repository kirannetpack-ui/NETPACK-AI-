<!-- PWA Install Floating Banner Component -->
<div x-data="{
        canInstall: false,
        dismissed: false
     }"
     x-init="
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            canInstall = false;
        } else {
            window.addEventListener('pwa-installable', () => {
                if (!sessionStorage.getItem('pwa_prompt_dismissed')) {
                    canInstall = true;
                }
            });
        }
     "
     x-show="canInstall && !dismissed"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-8"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-8"
     class="fixed bottom-20 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-20 sm:max-w-md z-50 bg-white/95 backdrop-blur-md border border-slate-200 rounded-lg p-3.5 shadow-xl shadow-teal-900/10"
     style="display: none;">
    <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-slate-800 flex items-center justify-center text-white text-lg shadow-sm flex-shrink-0">
            <i class="fas fa-mobile-screen"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h4 class="text-xs font-bold text-slate-900 leading-tight">NETPACK, on your home screen</h4>
            <p class="text-[11px] text-slate-500 leading-snug truncate">Open your courier workspace directly.</p>
        </div>
        <div class="flex items-center gap-1.5 flex-shrink-0">
            <button type="button"
                    @click="window.installNetpackApp(); dismissed = true"
                    class="px-3 py-1.5 bg-teal-600 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                Install
            </button>
            <button type="button"
                    @click="dismissed = true; sessionStorage.setItem('pwa_prompt_dismissed', '1')"
                    class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg text-xs"
                    aria-label="Dismiss">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
