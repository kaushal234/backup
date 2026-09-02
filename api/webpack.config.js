var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build')
    .addStyleEntry('css/emails', './assets/scss/emails.scss')
    .addStyleEntry('css/pdf', './assets/scss/pdf.scss')
    .addStyleEntry('css/chapter4', './assets/scss/chapter4.scss')
    .setPublicPath('/')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableSassLoader()
    .enableSingleRuntimeChunk()
;

module.exports = Encore.getWebpackConfig();
