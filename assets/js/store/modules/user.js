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

        setUserLoading(state) {
            state.userLoading = true
        },

        setUserLoaded(state) {
            state.userLoading = false
            state.userLoaded = true
        },
    },

    actions: {
        loadUser({commit}) {
            commit('setUserLoading')

            return userApi.get()
                .then(user => commit('setUser', user))
                .always(() => commit('setUserLoaded'))
        },
    },
}
