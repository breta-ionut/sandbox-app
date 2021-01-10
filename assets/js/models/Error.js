export default class Error {
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
     * @param {string} title
     * @param {number} code
     * @param {string|null} detail
     */
    constructor(title, code, detail) {
        this.#title = title
        this.#code = code
        this.#detail = detail
    }

    /**
     * @returns {Error}
     */
    static fromApiResponseData({title, code, detail}) {
        return new Error(title, code, detail)
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
}
