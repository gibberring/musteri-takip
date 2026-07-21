import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'public/crm_assets/css/bootstrap.min.css',
                'public/crm_assets/vendors/css/vendors.min.css',
                'public/crm_assets/css/theme.min.css'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
