const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the SCSS
 | file for the application as well as bundling up all the JS files.
 |
 */

let directory = {
    node: 'node_modules/',
    resource: 'resources/',
    destination: 'public/vue/',
}
directory.js = directory.destination+'js/';
directory.css = directory.destination+'css/';
directory.webfont = directory.destination+'webfonts/';
directory.fontAwesome = directory.node+'@fortawesome/fontawesome-free/';

mix
    .extract(['vue', 'lodash', 'axios'])
    .js(directory.resource+'js/app-home.js', directory.js).vue()
    .js(directory.resource+'js/app-stats.js', directory.js).vue()
    // font-awesome
    .copy(directory.fontAwesome+'css/all.min.css', directory.css+'font-awesome.css')
    .copy(directory.fontAwesome+'webfonts/fa-solid-900.woff2', directory.webfont+'fa-solid-900.woff2')
    .copy(directory.fontAwesome+'webfonts/fa-regular-400.woff2', directory.webfont+'fa-regular-400.woff2')

    .sass(directory.resource+'sass/app.scss', directory.css)

    /**
     * Note: Laravel wants to use postCss instead of SASS
     * That is why this is here and commented out.
     * We currently don't use postCss
     */
    // .postCss('resources/css/app.css', 'public/css', [
    //     //
    // ])

    .version();