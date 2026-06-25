import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { route } from '../../vendor/tightenco/ziggy';

// Make route() available globally on window so script blocks can call it directly
window.route = route;

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Also register as Vue global property so templates can use route() directly
        app.config.globalProperties.route = window.route;

        app.mount(el);
    },
    progress: {
        color: '#B8862E',
    },
});
