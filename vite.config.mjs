import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        {
            name: 'blade-reload',
            handleHotUpdate({ file, server }) {
                if (file.endsWith('.blade.php')) {
                    server.ws.send({
                        type: 'full-reload',
                        path: '*'
                    });
                }
            },
        },
    ],
    server: {
        host: 'localhost',
        port: 5173,
        cors: true,
        strictPort: true,
        watch: {
            usePolling: true,
            interval: 100,
        },
    },
});