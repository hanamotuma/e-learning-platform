import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { configureEcho } from '@laravel/echo-vue';
import './bootstrap'; 
import { createPinia } from 'pinia'; 

const pinia = createPinia();

configureEcho({
    broadcaster: 'reverb',
});

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            // --- ADD THIS SECTION BELOW ---
            .mixin({
                methods: {
                    checkInGroup(group: any) {
                        // This prevents the "Cannot convert undefined or null to object" error
                        if (!group || typeof group !== 'object') {
                            return false;
                        }
                        return Object.keys(group).length > 0;
                    }
                }
            })
            // --- END OF ADDED SECTION ---
            .mount(el);
    },
});