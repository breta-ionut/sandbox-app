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
        return axios.get('/user', {disableAutoLogout: !!disableAutoLogout})
            .then(response => User.fromApiResponseData(response.data))
    },

    /**
     * @param {Credentials} credentials
     *
     * @returns {Promise<User>}
     */
    async login(credentials) {
        return axios.post('/user/login', credentials).then(response => User.fromApiResponseData(response.data))
    },
}
