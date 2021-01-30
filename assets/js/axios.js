import axios from 'axios'

import authenticationRequestInterceptor from './api/authenticationRequestInterceptor.js'
import authenticationRequiredResponseInterceptor from './api/authenticationRequiredResponseInterceptor.js'
import errorResponseInterceptor from './api/errorResponseInterceptor.js'

axios.defaults.baseURL = API_BASE_URL
axios.defaults.headers = {'Content-Type': 'application/json', Accept: 'application/json'}

axios.interceptors.request.use(authenticationRequestInterceptor)
axios.interceptors.response.use(null, errorResponseInterceptor)
axios.interceptors.response.use(null, authenticationRequiredResponseInterceptor)

export default axios
