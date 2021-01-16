import errorCodes from './errorCodes.js'
import Error from '../models/Error.js'
import ValidationError from '../models/ValidationError.js'

const ERROR_UNKNOWN_TITLE = 'An error occurred.'

export default error => {
    if (!error.response?.data?.code) {
        throw new Error(ERROR_UNKNOWN_TITLE, errorCodes.UNKNOWN_ERROR, null)
    }

    switch (error.response.data.code) {
        case errorCodes.VALIDATION:
            throw ValidationError.fromApiResponseData(error.response.data)

        default:
            throw Error.fromApiResponseData(error.response.data)
    }
}
