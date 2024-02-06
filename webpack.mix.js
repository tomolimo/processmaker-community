const { mix } = require('laravel-mix');

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

mix.setPublicPath('workflow/public_html/webapp/')
    .setResourceRoot('/webapp/')
    .js('resources/assets/js/home/main.js', 'js/home')
    .sass('resources/assets/sass/app.scss', 'css/app.css')
    .js('resources/assets/js/admin/settings/customCaseList/main.js', 'js/admin/settings/customCaseList')
    .version();