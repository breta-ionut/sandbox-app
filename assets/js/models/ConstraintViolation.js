export default class ConstraintViolation {
    /**
     * @type {string|null}
     */
    #propertyPath

    /**
     * @type {string}
     */
    #title

    /**
     * @returns {ConstraintViolation}
     */
    static fromApiResponseData({propertyPath, title}) {
        const instance = new this()

        instance.#propertyPath = propertyPath
        instance.#title = title

        return instance
    }

    /**
     * @returns {boolean}
     */
    hasPropertyPath() {
        return !!this.#propertyPath
    }

    /**
     * @returns {string|null}
     */
    getPropertyPath() {
        return this.#propertyPath
    }

    /**
     * @returns {string}
     */
    getTitle() {
        return this.#title
    }
}
