'use strict'
const path = require('path')
const UglifyJSPlugin = require('uglifyjs-webpack-plugin')
const webpack = require('webpack')

const webpackBase = {
    entry: {
        inspection: ['./Resources/js/inspection.js'],
        dashboard: ['babel-polyfill','./Resources/js/dashboard/main.js']
    },
    watch:true,
    output: {
        path: path.resolve( './shop/dist/js'),
        filename: '[name].js'
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                exclude: [/node_modules/],
                loader: 'babel-loader'
            }
        ]
    },
    plugins: [
        /*new UglifyJSPlugin(),*/
    ]
}
webpackBase.devtool = 'cheap-module-eval-source-map'

module.exports = webpackBase