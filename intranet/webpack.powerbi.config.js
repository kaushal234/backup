const Encore = require('@symfony/webpack-encore');
const Dotenv = require('dotenv-webpack');
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");
const NodePolyfillPlugin = require("node-polyfill-webpack-plugin");


// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/en/private/build')
    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('build')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */

    .addEntry('powerbiServer', './assets/javascript/powerbi/server.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    .addPlugin(new Dotenv({
        systemvars: true
    }))
    .addPlugin(new NodePolyfillPlugin({
        additionalAliases: ['process'],
    }))
;

const mainConfig = Encore.getWebpackConfig();

const powerbiServerConfig = {
    ...mainConfig,
    entry: {
        powerbiServer: './assets/javascript/powerbi/server.js',
    },
    target: 'node', // Cible pour Node.js
    output: {
        filename: '[name].js',
        path: mainConfig.output.path,
        publicPath: mainConfig.output.publicPath,
    },
    optimization: {
        splitChunks: false,
    },
};

module.exports = powerbiServerConfig;