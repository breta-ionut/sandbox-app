'use strict'

const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const {VueLoaderPlugin} = require('vue-loader')
const {CleanWebpackPlugin} = require('clean-webpack-plugin')

module.exports = {
    entry: {
        index: path.resolve(__dirname, './js/index.js')
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
        }
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                ident: 'postcss',
                                plugins: [
                                    require('tailwindcss'),
                                    require('autoprefixer')
                                ]
                            }
                        }
                    }
                ]
            },
            {
                test: /\.vue$/,
                use: 'vue-loader'
            },
            {
                test: /\.js$/,
                use: 'babel-loader'
            }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin(),
        new VueLoaderPlugin(),
        new CleanWebpackPlugin()
    ]
}
