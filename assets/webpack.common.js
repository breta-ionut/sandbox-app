'use strict'

const path = require('path')
const {VueLoaderPlugin} = require('vue-loader')
const {CleanWebpackPlugin} = require('clean-webpack-plugin')

module.exports = {
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
            },
            {
                test: /\.js$/,
                use: 'babel-loader'
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
            }
        ]
    },
    plugins: [
        new VueLoaderPlugin(),
        new CleanWebpackPlugin()
    ]
}
