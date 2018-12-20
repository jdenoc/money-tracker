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

let cssDirectory            = 'public/vue/css';
let nodeDirectory           = 'node_modules';
let jsDirectory             = 'public/vue/js';
let webFontDirectory        = 'public/vue/webfonts';
let resourceAssetsDirectory = 'resources/assets';

mix.js('resources/assets/js/app.js', jsDirectory)
    // bulma-accordion
    .js(nodeDirectory+'/bulma-accordion/dist/bulma-accordion.min.js', jsDirectory+'/bulma-accordion.js')
    // dropzone
    .copy(nodeDirectory+'/vue2-dropzone/dist/vue2Dropzone.css', cssDirectory+'/vue-dropzone.css')
    // font-awesome
    .copy(nodeDirectory+'/@fortawesome/fontawesome-free/css/all.min.css', cssDirectory+'/font-awesome.css')
    .copy(nodeDirectory+'/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2', webFontDirectory+'/fa-solid-900.woff2')
    .copy(nodeDirectory+'/@fortawesome/fontawesome-free/webfonts/fa-regular-400.woff2', webFontDirectory+'/fa-regular-400.woff2')
    // tags-input
    .copy(nodeDirectory+'/@voerro/vue-tagsinput/dist/style.css', cssDirectory+'/tags-input.css')

    .sass(resourceAssetsDirectory+'/sass/app.scss', cssDirectory);