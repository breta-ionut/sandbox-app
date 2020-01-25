'use strict'

const merge = require('webpack-merge')
const commonConfig = require(__dirname + '/webpack.common.js')
const path = require('path')
const ManifestPlugin = require('webpack-manifest-plugin')

module.exports = merge(commonConfig, {
    mode: 'development',
    devtool: 'inline-source-map',
    devServer: {
        host: '0.0.0.0',
        port: 8080,
        https: true,
        contentBase: path.resolve(__dirname, '../public/build'),
        writeToDisk: (filename) => 'manifest.json' === path.basename(filename),
        allowedHosts: ['localhost'],
        headers: {'Access-Control-Allow-Origin': '*'},
        hot: true,
        watchOptions: {poll: true}
    },
    output: {publicPath: 'https://localhost:8080'},
    plugins: [
        new ManifestPlugin({publicPath: 'https://localhost:8080'})
    ]
})
