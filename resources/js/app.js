import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

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
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#047857', // Emerald Green loading indicator line matching the theme color
    },
});
