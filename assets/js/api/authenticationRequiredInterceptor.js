import errorCodes from './errorCodes.js'
import ApiError from '../errors/ApiError.js'
import router from '../router.js'
import store from '../store/index.js'

export default error => {
    if (error instanceof ApiError
        && error.getCode() === errorCodes.AUTHENTICATION_REQUIRED
        && !error.getOriginal().config?.disableAutoLogout
    ) {
        store.commit('user/logout')
        router.push({name: 'login'})
    }

    throw error
}
