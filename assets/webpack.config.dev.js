'use strict'

const path = require('path')
const webpack = require('webpack')
const { VueLoaderPlugin } = require('vue-loader')
const ManifestPlugin = require('webpack-manifest-plugin')
const CleanWebpackPlugin = require('clean-webpack-plugin')

module.exports = {
    mode: 'development',
    devServer: {
        host: '0.0.0.0',
        port: 8080,
        hot: true,
        watchOptions: {
            poll: true
        }
    },
    entry: {
        index: path.resolve(__dirname, './js/index.js')
    },
    output: {
        path: path.resolve(__dirname, '../public/build/js'),
        filename: '[name].js'
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                use: 'vue-loader'
            }
        ]
    },
    plugins: [
        new webpack.HotModuleReplacementPlugin(),
        new VueLoaderPlugin(),
        new ManifestPlugin(),
        new CleanWebpackPlugin()
    ]
}
