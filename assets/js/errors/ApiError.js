export default class ApiError extends Error {
    /**
     * @type {string}
     */
    #title

    /**
     * @type {number}
     */
    #code

    /**
     * @type {string|null}
     */
    #detail

    /**
     * @type {Object}
     */
    #original

    /**
     * @param {string} title
     * @param {number} code
     * @param {string|null} detail
     * @param {Object} original
     */
    constructor(title, code, detail, original) {
        super(title)

        this.#title = title
        this.#code = code
        this.#detail = detail
        this.#original = original
    }

    /**
     * @returns {ApiError}
     */
    static fromApiResponseData({title, code, detail}, original) {
        return new this(title, code, detail, original)
    }

    /**
     * @returns {string}
     */
    getTitle() {
        return this.#title
    }

    /**
     * @returns {number}
     */
    getCode() {
        return this.#code
    }

    /**
     * @returns {string|null}
     */
    getDetail() {
        return this.#detail
    }

    /**
     * @returns {Object}
     */
    getOriginal() {
        return this.#original
    }
}
