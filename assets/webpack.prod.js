'use strict'

const merge = require('webpack-merge')
const commonConfig = require(__dirname + '/webpack.common.js')

module.exports = merge(commonConfig, {
    mode: 'production',
    devtool: 'source-map'
})
