import errorCodes from './errorCodes.js'
import Error from '../models/Error.js'
import router from '../router.js'
import store from '../store/index.js'

export default error => {
    if (error instanceof Error && error.getCode() === errorCodes.AUTHENTICATION_REQUIRED) {
        store.commit('user/unsetUser')
        router.push({name: 'login'})
    }

    throw error
}
