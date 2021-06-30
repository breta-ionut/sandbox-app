import ApiValidationError from '../errors/ApiValidationError.js'

const noFormErrors = () => ({global: [], fields: {}})

export default {
    data: () => ({
        /**
         * @type {Object}
         */
        formErrors: noFormErrors(),
    }),

    methods: {
        resetFormErrors() {
            this.formErrors = noFormErrors()
        },

        /**
         * @param {ApiValidationError} error
         */
        handleApiValidationError(error) {
            this.formErrors.global.concat(error.getGlobalViolationTitles())
            Object.assign(this.formErrors.fields, error.getFieldsViolationTitles())
        },

        handleApiUnknownError() {
            this.formErrors.global.push('Unknown error occurred. Please try again.')
        },
    },
}
