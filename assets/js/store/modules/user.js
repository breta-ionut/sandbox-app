import User from '../../models/User.js'

export default {
    namespaced: true,

    state: () => ({
        /**
         * @type {User}
         */
        user: null,
    }),

    getters: {
        /**
         * @returns {string}
         */
        token: () => localStorage['user.token'],

        /**
         * @returns {boolean}
         */
        isTokenAvailable: (state, getters) => !!getters.token,
    },

    mutations: {
        /**
         * @param {Object} state
         * @param {User} user
         */
        set(state, user) {
            state.user = user

            localStorage['user.token'] = user.getCurrentToken().getToken()
        },
    },
}
