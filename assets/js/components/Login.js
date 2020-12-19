import userApi from '../api/user.js'
import errorCodes from '../api/errorCodes.js'
import Credentials from '../models/Credentials.js'
import ValidationError from '../models/ValidationError.js'

export default {
    data: () => ({
        username: '',
        password: '',
        errors: {},
    }),

    methods: {
        login() {
            userApi.login(Credentials.fromViewData(this.$data))
                .then((user) => this.$store.commit('user/set', user))
                .catch((error) => {
                    if (error instanceof ValidationError) {
                        this.errors.global = error.getGlobalViolationTitles()
                        this.errors.fields = error.getFieldsViolationTitles()
                    } else if (error.getCode() === errorCodes.AUTHENTICATION_FAILED) {
                        this.errors.global = ['Invalid credentials.']
                    } else {
                        this.errors.global = ['Unknown error occurred. Please try again.']
                    }
                })
        },
    },
}
