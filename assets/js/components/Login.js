import errorCodes from '../api/errorCodes.js'
import userApi from '../api/user.js'
import ApiError from '../errors/ApiError.js'
import ApiValidationError from '../errors/ApiValidationError.js'
import Credentials from '../models/Credentials.js'

const noErrors = () => ({global: [], fields: {}})

export default {
    data: () => ({
        username: '',
        password: '',
        errors: noErrors(),
    }),

    methods: {
        login() {
            this.errors = noErrors()

            userApi.login(Credentials.fromViewData(this.$data))
                .then(user => this.$store.commit('user/login', user))
                .catch(error => {
                    if (!error instanceof ApiError) {
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
                })
        },
    },
}
