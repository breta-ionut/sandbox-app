export default class ConstraintViolation {
    #propertyPath
    #title

    static fromApiResponseData({propertyPath, title}) {
        const instance = new ConstraintViolation()

        instance.#propertyPath = propertyPath
        instance.#title = title

        return instance
    }

    hasPropertyPath() {
        return !!this.#propertyPath
    }

    getPropertyPath() {
        return this.#propertyPath
    }

    getTitle() {
        return this.#title
    }
}
