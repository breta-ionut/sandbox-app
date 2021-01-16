import axios from 'axios'

import authenticationRequiredInterceptor from './api/authenticationRequiredInterceptor.js'
import authorizationInterceptor from './api/authorizationInterceptor.js'
import errorInterceptor from './api/errorInterceptor.js'

axios.defaults.baseURL = API_BASE_URL
axios.defaults.headers = {'Content-Type': 'application/json', Accept: 'application/json'}

axios.interceptors.request.use(authorizationInterceptor)
axios.interceptors.response.use(null, errorInterceptor)
axios.interceptors.response.use(null, authenticationRequiredInterceptor)

export default axios
