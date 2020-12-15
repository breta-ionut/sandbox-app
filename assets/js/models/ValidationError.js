import _ from 'lodash'

import ConstraintViolation from './ConstraintViolation.js'
import Error from './Error.js'

export default class ValidationError extends Error {
    violations = []

    static fromApiResponseData(data) {
        let validationError = _.assign(new ValidationError(), _.pick(['title', 'code', 'detail'], data))

        validationError.violations = data.violations.map(
            (violationData) => ConstraintViolation.fromApiResponseData(violationData)
        )

        return validationError
    }

    getViolations() {
        return this.violations
    }
}
