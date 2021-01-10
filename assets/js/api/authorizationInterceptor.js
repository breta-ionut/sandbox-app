import store from '../store/index.js'

export default config => {
    if (store.getters['user/isTokenAvailable']) {
        config.headers.Authorization = `Token ${store.getters['user/token']}`
    }

    return config
}
