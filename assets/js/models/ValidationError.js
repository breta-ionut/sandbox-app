import ConstraintViolation from './ConstraintViolation.js'
import Error from './Error.js'

export default class ValidationError extends Error {
    #violations = []

    static fromApiResponseData({title, code, detail, violations}) {
        let instance = new ValidationError(title, code, detail)

        instance.#violations = violations.map((violationData) => ConstraintViolation.fromApiResponseData(violationData))

        return instance
    }

    getViolations() {
        return this.#violations
    }
}
