import _ from 'lodash'

export default class User {
    id
    firstName
    lastName
    email
    plainPassword

    static fromViewData(data) {
        return _.assign(new User(), _.pick(['firstName', 'lastName', 'email', 'plainPassword'], data))
    }

    static fromApiResponseData(data) {
        return _.assign(new User(), _.pick(['id', 'firstName', 'lastName', 'email'], data))
    }
}
