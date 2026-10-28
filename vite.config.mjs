import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(), // <-- Tailwind integrado nativamente en Vite
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