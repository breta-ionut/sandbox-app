import userApi from '../api/user.js'

export default {
    data: () => ({
        username: null,
        password: null,
    }),

    methods: {
        login() {
            userApi.login({username: this.username, password: this.password})
        }
    }
}
