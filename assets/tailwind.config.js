'use strict'

const path = require('path')

module.exports = {
    purge: {
        mode: 'all',
        content: [
            path.resolve(__dirname, './js/**/*.vue'),
            path.resolve(__dirname, '../templates/frontend/**/*.html.php')
        ]
    }
}
