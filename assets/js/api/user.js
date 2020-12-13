import axios from '../axios.js'

export default {
    async get() {
        return axios.get('/user').then((response) => response.data)
    },

    async login(credentials) {
        return axios.post('/user/login', credentials).then((response) => response.data)
    },
}
