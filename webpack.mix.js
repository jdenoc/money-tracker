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
    destination: 'public/dist/'
}
directory.js = directory.destination+'js/';
directory.css = directory.destination+'styles/';

mix
    .extract(['vue', 'lodash', 'axios'])
    .js(directory.resource+'js/app-home.js', directory.js).vue()
    .js(directory.resource+'js/app-stats.js', directory.js).vue()
    .js(directory.resource+'js/app-settings.js', directory.js).vue()

    // tailwind specific
    .postCss(directory.resource+'styles/tailwind.css', directory.css+'app.css', [
        require("tailwindcss"),
    ])

    .version();