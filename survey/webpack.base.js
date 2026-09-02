'use strict'

const path = require('path')
const webpack = require('webpack')
const ExtractPlugin = require('extract-text-webpack-plugin')
const FriendlyErrorsWebpackPlugin = require('friendly-errors-webpack-plugin')

const webpackBase = {
  context: __dirname,
  entry: {
    app: ['./js/index.jsx']
  },
  stats: {
    colors: true,
    reasons: true,
    chunks: true
  },
  output: {
    path: path.join(__dirname, 'public'),
    filename: '[name].js'
  },
  resolve: {
    extensions: ['.js', '.jsx', '.json']
  },
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.jsx?$/,
        exclude: [/node_modules/, /assets\/(javascript|vendor)/],
        loader: 'eslint-loader'
      },
      {
        test: /\.jsx?$/,
        exclude: [/node_modules/],
        loader: 'babel-loader'
      },
      {
        test: /\.scss$/,
        use: ExtractPlugin.extract({
          use: ['css-loader', 'postcss-loader', 'sass-loader'],
          fallback: 'style-loader'
        })
      },
      {
        test: /\.less$/,
        use: ExtractPlugin.extract({
          use: ['css-loader', 'less-loader'],
          fallback: 'style-loader'
        })
      },
      {
        test: /\.css$/,
        use: ExtractPlugin.extract({
          use: ['css-loader', 'postcss-loader'],
          fallback: 'style-loader'
        })
      },
      {
        test: /\.(png|jpe?g|gif|wav|svg)(\?.*)?$/,
        use: [{
          loader: 'file-loader',
          query: {
            name: '[name].[ext]'
          }
        }]
      },
      {
        test: /\.(eot|svg|ttf|woff2?)(\?.*)?$/,
        use: [{
          loader: 'file-loader',
          query: {
            name: 'fonts/[name].[ext]'
          }
        }]
      }
    ]
  },
  plugins: [
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery'
    }),
    new webpack.optimize.CommonsChunkPlugin({
      name: 'vendors', // Specify the common bundle's name.
      minChunks: function (module) {
        if (module.resource && (/^.*\.(css|scss)$/).test(module.resource)) {
          return false
        }
          // this assumes your vendor imports exist in the node_modules directory
        return module.context && module.context.indexOf('node_modules') !== -1
      }
    }),
    new ExtractPlugin({
      filename: '[name].css'
    }),
    new FriendlyErrorsWebpackPlugin()
  ]
}

module.exports = webpackBase
