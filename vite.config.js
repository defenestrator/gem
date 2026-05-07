import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import basicSsl from '@vitejs/plugin-basic-ssl'

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const assetBase = mode === 'production' && env.ASSET_URL ? `${env.ASSET_URL}/build/` : undefined;

    return {
        base: assetBase,
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
                name: 'test',
                domains: ['*.gem.test'],
                ttlDays: 30,
                certDir: '/Users/jeremy/.devServer/cert'
            }),
        ],
    };
});
