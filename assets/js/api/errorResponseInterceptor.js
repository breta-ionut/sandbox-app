import errorCodes from './errorCodes.js'
import ApiAuthenticationRequiredError from '../errors/ApiAuthenticationRequiredError.js'
import ApiError from '../errors/ApiError.js'
import ApiValidationError from '../errors/ApiValidationError.js'

const ERROR_UNKNOWN_TITLE = 'An error occurred.'

export default error => {
    if (!error.response?.data?.code) {
        throw new ApiError(ERROR_UNKNOWN_TITLE, errorCodes.UNKNOWN_ERROR, null, error)
    }

    switch (error.response.data.code) {
        case errorCodes.VALIDATION:
            throw ApiValidationError.fromApiResponseData(error.response.data, error)

        case errorCodes.AUTHENTICATION_REQUIRED:
            throw ApiAuthenticationRequiredError.fromApiResponseData(error.response.data, error)

        default:
            throw ApiError.fromApiResponseData(error.response.data, error)
    }
}
