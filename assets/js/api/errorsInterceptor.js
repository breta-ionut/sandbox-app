import Error from '../models/Error.js'
import ValidationError from '../models/ValidationError.js'

const ERROR_TITLE_UNKNOWN = 'An error occurred.'

const ERROR_CODE_UNKNOWN = 100
const ERROR_CODE_VALIDATION = 103

export default function (error) {
    if (!error.response?.data?.code) {
        throw new Error(ERROR_TITLE_UNKNOWN, ERROR_CODE_UNKNOWN, null)
    }

    switch (error.response.data.code) {
        case ERROR_CODE_VALIDATION:
            throw ValidationError.fromApiResponseData(error.response.data)

        default:
            throw Error.fromApiResponseData(error.response.data)
    }
}
