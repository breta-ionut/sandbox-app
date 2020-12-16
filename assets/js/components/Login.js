import userApi from '../api/user.js'
import Credentials from '../models/Credentials.js'

export default {
    data: () => ({
        username: '',
        password: '',
    }),

    methods: {
        login() {
            userApi.login(Credentials.fromViewData(this)).then((user) => this.$store.commit('user/set', user))
        },
    },
}
