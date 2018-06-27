let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/vue/js')
    // font-awesome
    .copy('node_modules/@fortawesome/fontawesome-free/css/all.css', 'public/vue/css/font-awesome.css')
    .copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2', 'public/vue/webfonts/fa-solid-900.woff2')
    .copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-regular-400.woff2', 'public/vue/webfonts/fa-regular-400.woff2')
    // bulma-accordion
    .js('node_modules/bulma-accordion/dist/bulma-accordion.js', 'public/vue/js')
    .copy('node_modules/bulma-accordion/dist/bulma-accordion.min.css', 'public/vue/css/bulma-accordion.css')

    .sass('resources/assets/sass/app.scss', 'public/vue/css');