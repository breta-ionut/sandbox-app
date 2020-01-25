'use strict'

const path = require('path')
const webpack = require('webpack')
const { VueLoaderPlugin } = require('vue-loader')
const ManifestPlugin = require('webpack-manifest-plugin')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')

module.exports = {
    mode: 'development',
    devServer: {
        host: '0.0.0.0',
        port: 8080,
        contentBase: path.resolve(__dirname, '../public/build'),
        writeToDisk: (filename) => 'manifest.json' === path.basename(filename),
        allowedHosts: ['localhost', 'sandbox.local'],
        hot: true,
        watchOptions: {
            poll: true
        }
    },
    entry: {
        index: path.resolve(__dirname, './js/index.js')
    },
    output: {
        path: path.resolve(__dirname, '../public/build'),
        filename: (chunkData) => {
            const filename = '[name].js'

            switch (chunkData.contentHashType) {
                case 'javascript':
                    return 'js/' + filename

                default:
                    return filename
            }
        }
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
