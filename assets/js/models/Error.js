export default class Error {
    title
    code
    detail

    constructor(title, code, detail) {
        this.title = title
        this.code = code
        this.detail = detail
    }

    getTitle() {
        return this.title
    }

    getCode() {
        return this.code
    }

    getDetail() {
        return this.detail
    }
}
