import axios from '../axios.js'
import Credentials from '../models/Credentials.js'
import User from '../models/User.js'

export default {
    /**
     * @param {boolean|undefined} disableAutoLogout
     *
     * @returns {Promise<User>}
     */
    async get(disableAutoLogout) {
        return User.fromApiResponseData(await axios.get('/user', {disableAutoLogout}))
    },

    /**
     * @param {Credentials} credentials
     *
     * @returns {Promise<User>}
     */
    async login(credentials) {
        return User.fromApiResponseData(await axios.post('/user/login', credentials))
    },
}
