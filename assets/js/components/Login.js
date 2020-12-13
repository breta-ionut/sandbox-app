import {mapState} from 'vuex'

export default {
    data: () => ({
        username: null,
        password: null,
    }),

    computed: mapState('user', ['user']),

    methods: {
        login() {
            this.$store.dispatch('user/login', {username: this.username, password: this.password})
        },
    },

    created() {
        this.$store.dispatch('user/get')
    }
}
