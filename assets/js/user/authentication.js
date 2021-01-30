import User from '../models/User.js'
import router from '../router.js'
import store from '../store/index.js'

export default {
    /**
     * @returns {boolean}
     */
    hasToken: () => store.getters['user/hasToken'],

    /**
     * @returns {string}
     */
    getToken() {
        if (!this.hasToken()) {
            throw new Error('No authentication token available.')
        }

        return store.state.user.token
    },

    /**
     * @returns {Promise<void>}
     */
    async loadUser() {
        return store.dispatch('user/loadUser')
    },

    /**
     * @returns {boolean}
     */
    isAuthenticated() {
        return store.getters['user/isAuthenticated']
    },

    /**
     * @param {User} user
     *
     * @returns {Promise<any>}
     */
    async login(user) {
        store.commit('user/login', user)

        return router.push({name: 'home'})
    },

    /**
     * @returns {Promise<any>}
     */
    async logout() {
        store.commit('user/logout')

        return router.push({name: 'login'})
    },
};
