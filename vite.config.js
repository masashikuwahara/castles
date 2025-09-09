import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [
    vue(),
    laravel({
      input: ['resources/js/app.js'], // 追加のCSSがあればここに並べる
      refresh: true,
    }),
  ],
  resolve: { alias: { '@': path.resolve(__dirname, 'resources/js') } },
  server: {
    host: '127.0.0.1',
    port: 5173,
  },
})
