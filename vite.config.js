import { defineConfig } from 'vite';
import path from "path";
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ command, mode, isSsrBuild, isPreview }) => {
    return {
        plugins: [
            laravel({
                input: ['resources/styles/styles.scss', 'resources/scripts/scripts.ts'],
                refresh: true,
            }),
        ],
        server: {
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
        build: {
            rollupOptions: {
                output: {
                    entryFileNames: 'assets/[name].[hash].js',
                    chunkFileNames: 'assets/[name].[hash].js',
                    assetFileNames: (assetInfo) => {
                        const fileName = assetInfo.fileName || assetInfo.name || '';

                        if (typeof fileName === 'string' && fileName.endsWith('.css')) {
                            return 'assets/[name].[hash].[ext]';
                        }
                        
                        return 'assets/[name].[ext]';
                    },
                },
            },
        },
        resolve: {
            alias: {
                "~": path.resolve(__dirname, "resources"),
            },
        },
    };
});
