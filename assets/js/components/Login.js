export default {
    data: () => ({
        username: null,
        password: null,
    }),

    methods: {
        login() {
            this.$store.dispatch('user/login', {username: this.username, password: this.password})
        },
    },
}
