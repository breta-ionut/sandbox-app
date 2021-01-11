import User from '../../models/User.js'

export default {
    namespaced: true,

    state: () => ({
        /**
         * @type {User}
         */
        user: null,

        /**
         * @type {string|null}
         */
        token: localStorage['user.token'] || null,
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
        set(state, user) {
            state.user = user
            localStorage['user.token'] = state.token = user.getCurrentToken().getToken()
        },
    },
}
