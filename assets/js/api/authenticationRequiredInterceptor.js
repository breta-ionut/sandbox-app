import errorCodes from './errorCodes.js'
import Error from '../models/Error.js'
import store from '../store/index.js'

export default error => {
    if (error instanceof Error && error.getCode() === errorCodes.AUTHENTICATION_REQUIRED) {
        store.commit('user/requireAuthentication')
    }

    throw error
}
