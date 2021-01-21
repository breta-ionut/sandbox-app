'use strict'

const merge = require('webpack-merge')
const commonConfig = require(__dirname + '/webpack.common.js')
const path = require('path')
const AssetsManifestPlugin = require('webpack-assets-manifest')

module.exports = merge(commonConfig, {
    mode: 'development',
    devtool: 'inline-source-map',
    devServer: {
        host: '0.0.0.0',
        port: 8080,
        https: true,
        contentBase: path.resolve(__dirname, '../public/build'),
        allowedHosts: ['localhost', 'sandbox.local'],
        headers: {'Access-Control-Allow-Origin': '*'},
        hot: true,
        watchOptions: {poll: true},
    },
    output: {
        hotUpdateChunkFilename: '[id].[fullhash].hot-update.js',
        hotUpdateMainFilename: '[runtime].[fullhash].hot-update.json',
        publicPath: 'https://localhost:8080/',
    },
    plugins: [
        new AssetsManifestPlugin({output: 'manifest.json', writeToDisk: true, publicPath: 'https://localhost:8080/'}),
    ],
})
