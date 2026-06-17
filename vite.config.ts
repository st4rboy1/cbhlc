import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
        // Skip wayfinder in frontend-only deployments (e.g., Vercel) where PHP is not available
        process.env.SKIP_WAYFINDER !== 'true' && wayfinder({
            formVariants: true,
        }),
    ].filter(Boolean),
    esbuild: {
        jsx: 'automatic',
    },
});
