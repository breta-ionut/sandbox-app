import userApi from '../api/user.js'
import errorCodes from '../api/errorCodes.js'
import Credentials from '../models/Credentials.js'
import ValidationError from '../models/ValidationError.js'

const resetErrors = () => ({
    global: [],
    fields: {},
})

export default {
    data: () => ({
        username: '',
        password: '',
        errors: resetErrors(),
    }),

    methods: {
        login() {
            resetErrors()

            userApi.login(Credentials.fromViewData(this.$data))
                .then((user) => this.$store.commit('user/set', user))
                .catch((error) => {
                    if (error instanceof ValidationError) {
                        this.errors.global = error.getTitlesOfErrorsWithoutPropertyPath()
                        this.errors.fields = error.getErrorTitlesByPropertyPath()
                    } else if (error.getCode() === errorCodes.AUTHENTICATION_FAILED) {
                        this.errors.global.push('Invalid credentials.')
                    } else {
                        this.errors.global.push('Unknown error occurred. Please try again.')
                    }
                })
        },
    },
}
