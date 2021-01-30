import authentication from '../user/authentication.js'

export default config => {
    if (authentication.hasToken()) {
        config.headers.Authorization = `Token ${authentication.getToken()}`
    }

    return config
}
