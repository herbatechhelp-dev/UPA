import '../css/app.css';
import { createApp, h, ref } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import IslamicLoader from './Components/IslamicLoader.vue';

// ── Global loading state ──────────────────────────────────────────────
const isLoading       = ref(false);
const loaderMessage   = ref('Memuat...');
const isUpload        = ref(false);
const uploadProgress  = ref(0);

// ── Inertia router events — page navigation ───────────────────────────
router.on('start', () => {
    isUpload.value       = false;
    uploadProgress.value = 0;
    loaderMessage.value  = 'Membuka Halaman...';
    isLoading.value      = true;
});

router.on('finish', () => {
    isLoading.value      = false;
    isUpload.value       = false;
    uploadProgress.value = 0;
});

// ── Inertia upload progress ───────────────────────────────────────────
router.on('progress', (event) => {
    if (event.detail.progress?.percentage !== undefined) {
        isUpload.value       = true;
        loaderMessage.value  = 'Mengunggah File...';
        uploadProgress.value = Math.round(event.detail.progress.percentage);
    }
});

createInertiaApp({
    title: (title) => {
        try {
            const page = JSON.parse(document.getElementById('app').dataset.page);
            const appTitle = page.props.settings?.app_title || 'Unit Pembinaan Anggota';
            return `${title} - ${appTitle}`;
        } catch (e) {
            return `${title} - Unit Pembinaan Anggota`;
        }
    },
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Root wrapper that includes the loader overlay + the Inertia app
        const RootComponent = {
            setup() {
                return () => h('div', [
                    h(IslamicLoader, {
                        show: isLoading.value,
                        message: loaderMessage.value,
                        isUpload: isUpload.value,
                        uploadProgress: uploadProgress.value,
                    }),
                    h(App, props),
                ]);
            },
        };

        return createApp(RootComponent)
            .use(plugin)
            .mount(el);
    },
    progress: false, // Kita nonaktifkan progress bar bawaan Inertia karena sudah ada overlay kita
});
