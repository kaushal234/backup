const Encore = require('@symfony/webpack-encore');
const Dotenv = require('dotenv-webpack');
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");
const NodePolyfillPlugin = require("node-polyfill-webpack-plugin");
const webpack = require("webpack");
const FosRouting = require("fos-router/webpack/FosRouting");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

const configDotenv = {systemvars: true};
if (process.env.APP_ENV !== 'dev') {
    configDotenv.path = `.env.${process.env.APP_ENV}`;
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/build/")
  // public path used by the web server to access the output path
  .setPublicPath("/en/private/build")
  // only needed for CDN's or sub-directory deploy
  .setManifestKeyPrefix("build")

  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry("app", ["./assets", "/assets/react"])
  .addEntry("login", "./assets/login.js")
  .addEntry("tooltip", "./assets/javascript/tooltip_text.js")
  .addEntry("planner", "./assets/javascript/service/planner.js")
  .addEntry("iataMap", "./assets/javascript/iata_map.js")
  .addEntry("peopleMap", "./assets/javascript/directory/peopleMap.js")
  .addEntry("powerbiClient", "./assets/javascript/powerbi/client.ts")
  .addEntry("directoryPeople", "./assets/javascript/directory/acls.js")
  .addEntry(
    "positionsCategories",
    "./assets/javascript/directory/positions_categories.js"
  )
  .addEntry("salesCustomers", [
    "./assets/javascript/sales/bootstrap-treeview.min.js",
    "./assets/javascript/sales/customers.js",
  ])

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  .enableStimulusBridge("./assets/controllers.json")

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

  // uncomment if you use React
  .enableReactPreset()
  .enableTypeScriptLoader()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  //.enableIntegrityHashes(Encore.isProduction())

  .copyFiles([
    {
      from: "./node_modules/ckeditor4/",
      to: "ckeditor/[path][name].[ext]",
      pattern: /\.(js|css)$/,
      includeSubdirectories: false,
    },
    {
      from: "./node_modules/ckeditor4/adapters",
      to: "ckeditor/adapters/[path][name].[ext]",
    },
    {
      from: "./node_modules/ckeditor4/lang",
      to: "ckeditor/lang/[path][name].[ext]",
    },
    {
      from: "./node_modules/ckeditor4/plugins",
      to: "ckeditor/plugins/[path][name].[ext]",
    },
    {
      from: "./node_modules/ckeditor4/skins",
      to: "ckeditor/skins/[path][name].[ext]",
    },
    {
      from: "./node_modules/ckeditor4/vendor",
      to: "ckeditor/vendor/[path][name].[ext]",
    },
  ])

  .addPlugin(new Dotenv(configDotenv))
  .addPlugin(
    new NodePolyfillPlugin({
      additionalAliases: ["process"],
    })
  )
  .addPlugin(new FosRouting())
  .addPlugin(
    new BrowserSyncPlugin(
      {
        host: "localhost",
        port: 8085,
        proxy: "localhost",
        open: false,
        files: [
          // watch on changes
          {
            match: ["public/build/**/*.js"],
            fn: function (event, file) {
              if (event === "change") {
                const bs = require("browser-sync").get("bs-webpack-plugin");
                bs.reload();
              }
            },
          },
        ],
        notify: false,
      },
      {
        reload: false,
        name: "bs-webpack-plugin",
      }
    )
  )
  .addExternals({
    "bazinga-translator": "Translator",
  });

const mainConfig = Encore.getWebpackConfig();

mainConfig.resolve = {
  ...mainConfig.resolve,
  fallback: {
    ...mainConfig.resolve.fallback,
    fs: false,
    net: false,
    tls: false,
    path: require.resolve("path-browserify"),
    zlib: require.resolve("browserify-zlib"),
    http: require.resolve("stream-http"),
    https: require.resolve("https-browserify"),
    stream: require.resolve("stream-browserify"),
    crypto: require.resolve("crypto-browserify"),
    buffer: require.resolve("buffer"),
  },
};

mainConfig.plugins.push(new NodePolyfillPlugin());
mainConfig.plugins.push(
  new webpack.ProvidePlugin({
    process: "process/browser",
    Buffer: ["buffer", "Buffer"],
  })
);

mainConfig.module.rules.push({
  test: /\.txt$/,
  type: "asset/source",
});

module.exports = [mainConfig];