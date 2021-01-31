import errorCodes from '../api/errorCodes.js'
import userApi from '../api/user.js'
import ApiError from '../errors/ApiError.js'
import ApiValidationError from '../errors/ApiValidationError.js'
import Credentials from '../models/Credentials.js'
import authentication from '../user/authentication.js'

const noErrors = () => ({global: [], fields: {}})

export default {
    data: () => ({
        /**
         * @type {string}
         */
        username: '',

        /**
         * @type {string}
         */
        password: '',

        /**
         * @type {Object}
         */
        errors: noErrors(),
    }),

    methods: {
        /**
         * @returns {Promise<any>}
         */
        async login() {
            let user

            this.resetErrors()

            try {
                user = await userApi.login(Credentials.fromViewData(this.$data))
            } catch (error) {
                this.handleApiError(error)

                return
            }

            return authentication.login(user)
        },

        resetErrors() {
            this.errors = noErrors()
        },

        handleApiError(error) {
            if (!(error instanceof ApiError)) {
                throw error
            }

            if (error instanceof ApiValidationError) {
                this.errors.global.concat(error.getGlobalViolationTitles())
                Object.assign(this.errors.fields, error.getFieldsViolationTitles())
            } else if (error.getCode() === errorCodes.AUTHENTICATION_FAILED) {
                this.errors.global.push('Invalid credentials.')
            } else {
                this.errors.global.push('Unknown error occurred. Please try again.')
            }
        },
    },
}
