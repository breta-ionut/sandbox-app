import userApi from '../../api/user.js'
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

        /**
         * @type {boolean}
         */
        userLoading: false,

        /**
         * @type {boolean}
         */
        userLoaded: false,
    }),

    getters: {
        /**
         * @returns {boolean}
         */
        hasToken: state => !!state.token,

        /**
         * @returns {boolean}
         */
        isAuthenticated: state => !!state.user,
    },

    mutations: {
        /**
         * @param {Object} state
         * @param {User} user
         */
        login(state, user) {
            state.user = user
            localStorage['user/token'] = state.token = user.getCurrentToken().getToken()
        },

        logout(state) {
            state.user = null
            localStorage['user/token'] = state.token = null
        },

        markUserAsLoading(state) {
            state.userLoading = true
        },

        markUserAsLoaded(state) {
            state.userLoading = false
            state.userLoaded = true
        },
    },

    actions: {
        getUser({commit}) {
            commit('markUserAsLoading')

            return userApi.get()
                .then(user => commit('login', user))
                .finally(() => commit('markUserAsLoaded'))
        },
    },
}
