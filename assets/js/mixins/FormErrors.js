import ApiValidationError from '../errors/ApiValidationError.js'

/**
 * @return {Object}
 */
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
         * @param {string} error
         */
        addFormGlobalError(error) {
            this.formErrors.global.push(error)
        },

        /**
         * @param {string} field
         * @param {string} error
         */
        addFormFieldError(field, error) {
            if (undefined === this.formErrors.fields[field]) {
                this.formErrors.fields[field] = []
            }

            this.formErrors.fields[field].push(error)
        },

        /**
         * @param {ApiValidationError} error
         */
        addFormErrorsFromApiValidationError(error) {
            this.formErrors.global.concat(error.getGlobalViolationTitles())
            Object.assign(this.formErrors.fields, error.getFieldsViolationTitles())
        },

        addFormUnknownError() {
            this.addFormGlobalError('Unknown error occurred. Please try again.')
        },
    },
}
