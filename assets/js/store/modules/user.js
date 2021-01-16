import User from '../../models/User.js'

export default {
    namespaced: true,

    state: () => ({
        /**
         * @type {string|null}
         */
        token: localStorage['user/token'] || null,

        /**
         * @type {User|null}
         */
        user: null,
    }),

    getters: {
        /**
         * @returns {boolean}
         */
        hasToken: state => !!state.token,
    },

    mutations: {
        /**
         * @param {Object} state
         * @param {User} user
         */
        setUser(state, user) {
            state.user = user
            localStorage['user/token'] = state.token = user.getCurrentToken().getToken()
        },

        unsetUser(state) {
            state.user = null
            localStorage['user/token'] = state.token = null
        },
    },
}
