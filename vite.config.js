import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    // Dev server runs on localhost:5173 but the app is served from subdomain hosts
    // like crossfit.projectfit.local:8888 — those are different origins, so the
    // browser blocks the dev-server script fetches unless we allow CORS.
    server: {
        cors: true,
        host: '0.0.0.0',
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/layout.js',
                'resources/js/apps/signup.js',
                'resources/js/apps/dashboard.js',
                'resources/js/questionnaire-builder.js',
                'resources/js/schedule-calendar.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
