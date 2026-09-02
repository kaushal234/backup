'use strict'

const webpack = require('webpack')
const webpackBase = require('./webpack.config')

webpackBase.watch = false
webpackBase.plugins.push(
  new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: JSON.stringify('production')
    }
  }),
  new webpack.optimize.UglifyJsPlugin({
    compress: { warnings: false },
    comments: false
  })
)

module.exports = webpackBase
