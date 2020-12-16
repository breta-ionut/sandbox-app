import _ from 'lodash'

export default class Credentials {
    username
    password

    static fromViewData(data) {
        return _.assign(new Credentials(), _.pick(['username', 'password'], data))
    }
}
