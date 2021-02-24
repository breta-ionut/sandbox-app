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
        let response = await axios.get('/user', {disableAutoLogout})

        return User.fromApiResponseData(response.data)
    },

    /**
     * @param {Credentials} credentials
     *
     * @returns {Promise<User>}
     */
    async login(credentials) {
        let response = await axios.post('/user/login', credentials)

        return User.fromApiResponseData(response.data)
    },

    async logout() {
        await axios.delete('/user/logout')
    },
}
