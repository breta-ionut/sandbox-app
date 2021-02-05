import Credentials from '../models/Credentials.js'
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
    getToken: () => store.getters['user/getToken'],

    loadUser: async () => await store.dispatch('user/loadUser'),

    /**
     * @returns {boolean}
     */
    isAuthenticated: () => store.getters['user/isAuthenticated'],

    /**
     * @param {Credentials} credentials
     */
    login: async credentials => {
        await store.dispatch('user/login', credentials)
        await router.push({name: 'dashboard'})
    },

    logout: async () => {
        store.commit('user/unsetUser')
        await router.push({name: 'login'})
    },
}
