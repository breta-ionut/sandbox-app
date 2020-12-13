import axios from '../axios.js'

export default {
    async get() {
        return axios.get('/user')
    },

    async login(credentials) {
        return axios.post('/user/login', credentials)
    },
}
