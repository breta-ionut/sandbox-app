'use strict'

const path = require('path')
const {VueLoaderPlugin} = require('vue-loader')
const {CleanWebpackPlugin} = require('clean-webpack-plugin')

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
                            importLoaders: 1
                        }
                    },
                    'postcss-loader'
                ]
            },
            {
                test: /\.vue$/,
                use: 'vue-loader'
            },
            {
                test: /\.js$/,
                use: 'babel-loader'
            },
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
        new CleanWebpackPlugin(),
    ],
}
