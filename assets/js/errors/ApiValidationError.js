import ApiError from './ApiError.js'
import ConstraintViolation from '../models/ConstraintViolation.js'

export default class ApiValidationError extends ApiError {
    /**
     * @type {ConstraintViolation[]}
     */
    #violations = []

    /**
     * @returns {ApiValidationError}
     */
    static fromApiResponseData({violations, ...data}, original) {
        let instance = super.fromApiResponseData(data)

        instance.#violations = violations.map(violationData => ConstraintViolation.fromApiResponseData(violationData))

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
            .filter(violation => !violation.hasPropertyPath())
            .map(violation => violation.getTitle())
    }

    /**
     * @returns {Object}
     */
    getFieldsViolationTitles() {
        const violationTitles = {}

        this.#violations.forEach(violation => {
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
