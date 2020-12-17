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

    getTitlesOfErrorsWithoutPropertyPath() {
        return this.#violations
            .filter((violation) => !violation.propertyPath)
            .map((violation) => violation.title)
    }

    getErrorTitlesByPropertyPath() {
        const errorTitles = {}

        this.#violations.forEach((violation) => {
            if (undefined === errorTitles[violation.propertyPath]) {
                errorTitles[violation.propertyPath] = []
            } else {
                errorTitles[violation.propertyPath].push(violation.title)
            }
        })

        return errorTitles
    }
}
