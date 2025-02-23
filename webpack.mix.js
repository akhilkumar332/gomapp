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
    .js('resources/js/components.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css')
    .postCss('resources/css/components.css', 'public/css')
    .version();

// Copy boxicons webfont files
mix.copy('node_modules/boxicons/fonts', 'public/fonts/boxicons');

// Production specific configuration
if (mix.inProduction()) {
    mix.options({
        terser: {
            terserOptions: {
                compress: {
                    drop_console: true
                }
            }
        },
        cssNano: {
            discardComments: {
                removeAll: true
            }
        }
    });
}

// Development specific configuration
if (!mix.inProduction()) {
    mix.sourceMaps();
    mix.browserSync({
        proxy: 'localhost:8000',
        open: false,
        notify: false
    });
}

// Common configuration
mix.options({
    processCssUrls: false,
    postCss: [
        require('postcss-import'),
        require('tailwindcss'),
        require('autoprefixer')
    ]
});

// Extract vendor libraries
mix.extract([
    'jquery',
    'bootstrap',
    'sweetalert2',
    'chart.js',
    'alpinejs'
]);

// Copy third-party assets
mix.copyDirectory('resources/images', 'public/images');

// Add version hash in production
if (mix.inProduction()) {
    mix.version();
}

// Add custom Webpack configuration
mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@components': path.resolve(__dirname, 'resources/js/components'),
            '@css': path.resolve(__dirname, 'resources/css')
        }
    },
    output: {
        chunkFilename: 'js/chunks/[name].[chunkhash].js'
    }
});

// Add performance hints
mix.webpackConfig({
    performance: {
        hints: false,
        maxEntrypointSize: 512000,
        maxAssetSize: 512000
    }
});

// Add bundle analyzer in production
if (mix.inProduction()) {
    const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
    
    mix.webpackConfig({
        plugins: [
            new BundleAnalyzerPlugin({
                analyzerMode: 'static',
                openAnalyzer: false
            })
        ]
    });
}

// Add environment specific configuration
if (process.env.npm_lifecycle_event === 'hot') {
    mix.options({
        hmrOptions: {
            host: 'localhost',
            port: 8080
        }
    });
}

// Add polyfills for older browsers
mix.webpackConfig({
    entry: {
        polyfills: './resources/js/polyfills.js'
    }
});

// Add support for TypeScript
mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                loader: 'ts-loader',
                exclude: /node_modules/
            }
        ]
    },
    resolve: {
        extensions: ['*', '.js', '.jsx', '.ts', '.tsx']
    }
});
