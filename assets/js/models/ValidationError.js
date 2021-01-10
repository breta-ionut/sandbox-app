import ConstraintViolation from './ConstraintViolation.js'
import Error from './Error.js'

export default class ValidationError extends Error {
    /**
     * @type {ConstraintViolation[]}
     */
    #violations = []

    /**
     * @returns {ValidationError}
     */
    static fromApiResponseData({title, code, detail, violations}) {
        let instance = new ValidationError(title, code, detail)

        instance.#violations = violations.map((violationData) => ConstraintViolation.fromApiResponseData(violationData))

        return instance
    }

    /**
     * @returns {ConstraintViolation[]}
     */
    getViolations() {
        return this.#violations
    }

    /**
     * @returns {string[]}
     */
    getGlobalViolationTitles() {
        return this.#violations
            .filter((violation) => !violation.hasPropertyPath())
            .map((violation) => violation.getTitle())
    }

    /**
     * @returns {Object}
     */
    getFieldsViolationTitles() {
        const violationTitles = {}

        this.#violations.forEach((violation) => {
            let propertyPath

            if (!violation.hasPropertyPath()) {
                return
            }

            propertyPath = violation.getPropertyPath()

            if (undefined === violationTitles[propertyPath]) {
                violationTitles[propertyPath] = [violation.getTitle()]
            } else {
                violationTitles[propertyPath].push(violation.getTitle())
            }
        })

        return violationTitles
    }
}
