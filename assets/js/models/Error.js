export default class Error {
    #title
    #code
    #detail

    constructor(title, code, detail) {
        this.#title = title
        this.#code = code
        this.#detail = detail
    }

    static fromApiResponseData({title, code, detail}) {
        return new Error(title, code, detail)
    }

    getTitle() {
        return this.#title
    }

    getCode() {
        return this.#code
    }

    getDetail() {
        return this.#detail
    }
}
