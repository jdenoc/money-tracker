const mix = require('laravel-mix');

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

let cssDirectory      = 'public/vue/css';
let nodeDirectory     = 'node_modules';
let jsDirectory       = 'public/vue/js';
let webFontDirectory  = 'public/vue/webfonts';
let resourceDirectory = 'resources';

mix
    .js(resourceDirectory+'/js/app-home.js', jsDirectory).vue()
    .js(resourceDirectory+'/js/app-stats.js', jsDirectory).vue()
    .extract(['vue', 'lodash', 'axios'])
    // font-awesome
    .copy(nodeDirectory+'/@fortawesome/fontawesome-free/css/all.min.css', cssDirectory+'/font-awesome.css')
    .copy(nodeDirectory+'/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2', webFontDirectory+'/fa-solid-900.woff2')
    .copy(nodeDirectory+'/@fortawesome/fontawesome-free/webfonts/fa-regular-400.woff2', webFontDirectory+'/fa-regular-400.woff2')

    .sass(resourceDirectory+'/sass/app.scss', cssDirectory)
    .version();