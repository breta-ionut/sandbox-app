'use strict'

const path = require('path')
const {CleanWebpackPlugin} = require('clean-webpack-plugin')

module.exports = {
    mode: 'production',
    entry: {
        api_doc: path.resolve(__dirname, './js/api_doc.js'),
    },
    output: {
        path: path.resolve(__dirname, '../public/build/api_doc'),
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            },
            {
                test: /\.js$/,
                use: 'babel-loader'
            },
        ],
    },
    plugins: [
        new CleanWebpackPlugin(),
    ],
}
