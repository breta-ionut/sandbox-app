'use strict'

const path = require('path')
const {VueLoaderPlugin} = require('vue-loader')
const {CleanWebpackPlugin} = require('clean-webpack-plugin')

module.exports = {
    entry: {
        index: path.resolve(__dirname, './js/index.js'),
        swagger: path.resolve(__dirname, './js/swagger.js')
    },
    output: {
        path: path.resolve(__dirname, '../public/build'),
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
                test: /\.vue$/,
                use: 'vue-loader'
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            },
            {
                test: /\.sass$/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: true,
                            sassOptions: {compressed: true}
                        }
                    }
                ]
            },
            {
                test: /\.js$/,
                use: 'babel-loader'
            }
        ]
    },
    plugins: [
        new VueLoaderPlugin(),
        new CleanWebpackPlugin()
    ]
}
