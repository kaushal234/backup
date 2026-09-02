'use strict'

const webpack = require('webpack')
const ProgressBarPlugin = require('progress-bar-webpack-plugin')
const webpackBase = require('./webpack.base')
const CompressionPlugin = require('compression-webpack-plugin')

webpackBase.plugins.push(
  new ProgressBarPlugin(),
  new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: JSON.stringify('production')
    }
  }),
  new webpack.optimize.UglifyJsPlugin({
    compress: { warnings: false },
    comments: false
  }),
  new webpack.optimize.AggressiveMergingPlugin(),
  new CompressionPlugin({
    asset: '[path].gz[query]',
    algorithm: 'gzip',
    test: /\.js$|\.css$$/,
    threshold: 10240,
    minRatio: 0.8
  })
)

module.exports = webpackBase
