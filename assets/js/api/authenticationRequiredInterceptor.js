import errorCodes from './errorCodes.js'
import ApiError from '../errors/ApiError.js'
import store from '../store/index.js'

export default error => {
    if (error instanceof ApiError && error.getCode() === errorCodes.AUTHENTICATION_REQUIRED) {
        store.commit('user/requireAuthentication')
    }

    throw error
}
