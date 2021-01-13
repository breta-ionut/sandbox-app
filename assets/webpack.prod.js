'use strict'

const merge = require('webpack-merge')
const commonConfig = require(__dirname + '/webpack.common.js')
const AssetsManifestPlugin = require('webpack-assets-manifest');

module.exports = merge(commonConfig, {
    mode: 'production',
    devtool: 'source-map',
    plugins: [
        new AssetsManifestPlugin({output: 'manifest.json', publicPath: '/build/app/'}),
    ],
})
