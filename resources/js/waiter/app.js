import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { createVuetify } from 'vuetify';
import 'vuetify/styles';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi';

const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#DC2626',
                    'primary-darken-1': '#B91C1C',
                    'primary-50': '#FEF2F2',
                    'primary-100': '#FEE2E2',
                    'primary-200': '#FECACA',
                    'primary-300': '#FCA5A5',
                    'primary-400': '#F87171',
                    'primary-500': '#EF4444',
                    'primary-600': '#DC2626',
                    'primary-700': '#B91C1C',
                    'primary-800': '#991B1B',
                    'primary-900': '#7F1D1D',
                    secondary: '#64748B',
                    accent: '#F8FAFC',
                    error: '#DC2626',
                    info: '#2563EB',
                    success: '#16A34A',
                    warning: '#F59E0B',
                    background: '#F8FAFC',
                    surface: '#FFFFFF',
                    'surface-variant': '#F8FAFC',
                    'on-surface': '#0F172A',
                    'on-surface-variant': '#0F172A',
                },
            },
        },
    },
    defaults: {
        global: {
            rounded: 'lg',
        },
        VBtn: {
            style: {
                textTransform: 'none',
            },
        },
        VCard: {
            elevation: 2,
        },
    },
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
            mdi,
        },
    },
});

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(vuetify)
            .mount(el);
    },
});
