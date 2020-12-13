export default {
    data: () => ({
        username: null,
        password: null,
    }),

    methods: {
        login() {
            this.$store.dispatch('login', {username: this.username, password: this.password})
        },
    },
}
