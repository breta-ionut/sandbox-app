import store from '../store/index.js'

export default config => {
    if (store.getters['user/hasToken']) {
        config.headers.Authorization = `Token ${store.state.user.token}`
    }

    return config
}
