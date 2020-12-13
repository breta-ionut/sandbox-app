import axios from '../axios.js'

export default {
    async login(credentials) {
        return axios.post('/user/login', credentials)
    },
}
