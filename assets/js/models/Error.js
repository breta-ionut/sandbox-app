import _ from 'lodash'

export default class Error {
    title
    code
    detail

    static fromApiResponseData(data) {
        return _.assign(new Error(), _.pick(['title', 'code', 'detail'], data))
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
