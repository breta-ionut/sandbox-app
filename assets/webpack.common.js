'use strict'

const path = require('path')
const webpack = require('webpack')
const {VueLoaderPlugin} = require('vue-loader')
const {CleanWebpackPlugin} = require('clean-webpack-plugin')

const globals = require('./js/globals.js')

module.exports = {
    entry: {
        index: path.resolve(__dirname, './js/index.js'),
    },
    output: {
        path: path.resolve(__dirname, '../public/build/app'),
        filename: (pathData) => {
            switch (pathData.contentHashType) {
                case 'javascript':
                    return 'js/[name].js'

                default:
                    throw new Error(
                        `Entry "${pathData.chunk.name}" contains unknown content type "${pathData.contentHashType}".`
                    )
            }
        },
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    'style-loader',
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 1,
                        },
                    },
                    'postcss-loader',
                ],
            },
            {
                test: /\.vue$/,
                use: 'vue-loader',
            },
            {
                test: /\.js$/,
                use: 'babel-loader',
            },
            {
                test: /\.(png|svg|jpg|jpeg|gif)$/i,
                type: 'asset/resource',
            },
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
        new CleanWebpackPlugin(),
        new webpack.DefinePlugin(globals),
    ],
}
