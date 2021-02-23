export default {
    data: () => ({
        /**
         * @type {boolean}
         */
        showUserMenu: false,
    }),

    methods: {
        toggleUserMenu() {
            this.showUserMenu = !this.showUserMenu
        },

        hideUserMenu() {
            this.showUserMenu = false
        },
    }
}
