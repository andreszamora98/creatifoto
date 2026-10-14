import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/auth.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    server: {   
        host: '192.168.100.94', //Dirección IP de la Mac
        port: 5173,
        strictPort: true,
        cors: {
            origin: 'http://192.168.100.94:8000', //Dirección IP de la Mac
            methods: ['GET', 'HEAD', 'PUT', 'PATCH', 'POST', 'DELETE'],
            credentials: true,
        },
    },
});