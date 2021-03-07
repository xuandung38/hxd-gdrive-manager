const mix = require('laravel-mix');
const webpack = require('webpack');
var path = require('path');
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

mix.sass('resources/sass/app.scss', 'public/css')
    .js('resources/js/file-manager.js', 'public/js')
    .js('resources/js/client.js', 'public/js')
    .sass('resources/sass/file-manager.scss', 'public/css')
    .copy('node_modules/element-ui/lib/theme-chalk/index.css', 'public/css/element-ui.min.css')
    .copy('node_modules/element-ui/lib/theme-chalk/fonts', 'public/css/fonts')
    .copy('resources/img', 'public/images')
    .version()
    .sourceMaps();
