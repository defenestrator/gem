import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import basicSsl from '@vitejs/plugin-basic-ssl'

export default defineConfig({
    server: {
        https: true,
        host: 'gem.test',
        port: 5173,
        hmr: {
            host: 'gem.test',
            port: 5173,
        },
        cors: true,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
            basicSsl({
        /** name of certification */
        name: 'test',
        /** custom trust domains */
        domains: ['*.gem.test'],
        /** optional, days before certificate expires */
        ttlDays: 30,
        /** custom certification directory */
        certDir: '/Users/jeremy/.devServer/cert'
        }),
    ],
});
