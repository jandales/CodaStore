const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    // .postCss('resources/css/app.css', 'public/css', [
    //     //
    // ])
    .css('resources/css/app.css', 'public/css/app.css')
    .css('resources/css/main.css', 'public/css/main.css')
    // .scripts([        
    //     'resources/js/admin/blogs.js',
    // ], 'public/js/main.js')
