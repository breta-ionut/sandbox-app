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
            .filter((violation) => !violation.hasPropertyPath())
            .map((violation) => violation.title)
    }

    getErrorTitlesByPropertyPath() {
        const errorTitles = {}

        this.#violations.forEach((violation) => {
            let propertyPath

            if (!violation.hasPropertyPath()) {
                return
            }

            propertyPath = violation.getPropertyPath()

            if (undefined === errorTitles[propertyPath]) {
                errorTitles[propertyPath] = [violation.getTitle()]
            } else {
                errorTitles[propertyPath].push(violation.getTitle())
            }
        })

        return errorTitles
    }
}
