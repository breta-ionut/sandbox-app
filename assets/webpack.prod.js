'use strict'

const merge = require('webpack-merge')
const commonConfig = require(__dirname + '/webpack.common.js')
const ManifestPlugin = require('webpack-manifest-plugin');

module.exports = merge(commonConfig, {
    mode: 'production',
    devtool: 'source-map',
    plugins: [
        new ManifestPlugin({publicPath: '/build/app/'})
    ],
})
