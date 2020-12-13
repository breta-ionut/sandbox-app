import axios from '../axios.js'

export default {
    login(credentials) {
        return axios.post('/user/login', credentials)
    },
}
