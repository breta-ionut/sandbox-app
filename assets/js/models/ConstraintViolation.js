import _ from 'lodash'

export default class ConstraintViolation {
    propertyPath
    title

    static fromApiResponseData(data) {
        return _.assign(new ConstraintViolation(), _.pick(['propertyPath', 'title'], data))
    }

    getPropertyPath() {
        return this.propertyPath
    }

    getTitle() {
        return this.title
    }
}
