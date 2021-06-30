import errorCodes from '../api/errorCodes.js'
import ApiError from '../errors/ApiError.js'
import ApiValidationError from '../errors/ApiValidationError.js'
import FormErrors from '../mixins/FormErrors.js'
import Credentials from '../models/Credentials.js'
import authentication from '../user/authentication.js'

export default {
    mixins: [FormErrors],

    data: () => ({
        /**
         * @type {Object}
         */
        credentials: {
            /**
             * @type {string}
             */
            username: '',

            /**
             * @type {string}
             */
            password: '',
        },
    }),

    methods: {
        async login() {
            this.resetFormErrors()

            try {
                await authentication.login(Credentials.fromViewData(this.credentials))
            } catch (error) {
                this.handleApiError(error)
            }
        },

        handleApiError(error) {
            if (!(error instanceof ApiError)) {
                throw error
            }

            if (error instanceof ApiValidationError) {
                this.addFormErrorsFromApiValidationError(error)
            } else if (error.getCode() === errorCodes.AUTHENTICATION_FAILED) {
                this.addFormGlobalError('Invalid credentials.')
            } else {
                this.addFormUnknownError()
            }
        },
    },
}
