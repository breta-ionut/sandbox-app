import errorCodes from '../../api/errorCodes.js'
import userApi from '../../api/user.js'
import ApiAuthenticationRequiredError from '../../errors/ApiAuthenticationRequiredError.js'
import User from '../../models/User.js'

/**
 * @param {Object} state
 *
 * @throws {Error}
 */
const ensureUserIsLoaded = state => {
    if (!state.userLoaded) {
        throw new Error('The user is not loaded yet.')
    }
}

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
        isAuthenticated: state => {
            ensureUserIsLoaded(state)

            return !!state.user
        },
    },

    mutations: {
        /**
         * @param {Object} state
         * @param {User} user
         */
        login(state, user) {
            localStorage['user/token'] = state.token = user.getCurrentToken().getToken()
            state.user = user
            state.userLoaded = true
        },

        logout(state) {
            state.user = state.token = null
            delete localStorage['user/token']
            state.userLoaded = true
        },
    },

    actions: {
        loadUser({state, commit}) {
            if (state.userLoaded) {
                return Promise.resolve()
            }

            return userApi.get(true)
                .then(user => commit('login', user))
                .catch(error => {
                    if (!(error instanceof ApiAuthenticationRequiredError)) {
                        throw error
                    }

                    commit('logout')
                })
        },
    },
}
