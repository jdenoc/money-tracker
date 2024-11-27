import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/styles/tailwind.css',
        'resources/js/app-home.js',
        'resources/js/app-stats.js',
        'resources/js/app-settings.js',
      ],
      refresh: true,
    }),
    vue(),
  ],
  resolve: {
    alias: {
      // needed for vue 2
      'vue': 'vue/dist/vue.esm.js',
    },
  },
});
