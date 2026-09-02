'use strict'

const webpack = require('webpack')
const webpackBase = require('./webpack.base')

webpackBase.devtool = 'cheap-module-eval-source-map'
webpackBase.devServer = {
  port: 8086,
  hot: true,
  contentBase: 'public',
  headers: { 'Access-Control-Allow-Origin': '*' },
  historyApiFallback: {
    disableDotRule: true
  },
  overlay: true
}

webpackBase.plugins.push(
  new webpack.HotModuleReplacementPlugin(),
  new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: JSON.stringify('development')
    }
  })
)

module.exports = webpackBase
