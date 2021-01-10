import store from '../store/index.js'

export default config => {
    if (store.getters.isTokenAvailable) {
        config.headers.Authorization = `Token ${store.getters.token}`
    }

    return config
}
