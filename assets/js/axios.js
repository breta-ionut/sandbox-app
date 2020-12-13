import axios from 'axios'

axios.defaults.baseURL = API_BASE_URL
axios.defaults.headers = {'Content-Type': 'application/json', 'Accept': 'application/json'}

export default axios
