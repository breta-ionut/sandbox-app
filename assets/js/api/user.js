import axios from '../axios.js'
import User from '../models/User.js'

export default {
    /**
     * @returns {Promise<User>}
     */
    async get() {
        return axios.get('/user').then((response) => User.fromApiResponseData(response.data))
    },

    /**
     * @param {Credentials} credentials
     *
     * @returns {Promise<User>}
     */
    async login(credentials) {
        return axios.post('/user/login', credentials).then((response) => User.fromApiResponseData(response.data))
    },
}
