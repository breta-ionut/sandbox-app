import errorCodes from '../../api/errorCodes.js'
import userApi from '../../api/user.js'
import ApiError from '../../errors/ApiError.js'
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
            state.token = state.user = null
            delete localStorage['user/token']
        },

        markUserAsLoaded(state) {
            state.userLoaded = true
        },
    },

    actions: {
        getUser({state, commit}) {
            if (state.userLoaded) {
                return Promise.resolve(state.user)
            }

            return userApi.get(true)
                .then(user => commit('login', user))
                .catch(error => {
                    if (!(error instanceof ApiError) || error.getCode() !== errorCodes.AUTHENTICATION_REQUIRED) {
                        throw error
                    }

                    commit('logout')
                })
                .then(() => {
                    commit('markUserAsLoaded')

                    return state.user
                })
        },
    },
}
