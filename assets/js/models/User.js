import Token from './Token.js'

export default class User {
    #id
    #firstName
    #lastName
    #email
    #plainPassword
    #currentToken

    static fromViewData({firstName, lastName, email, plainPassword}) {
        let instance = new User()

        instance.#firstName = firstName
        instance.#lastName = lastName
        instance.#email = email
        instance.#plainPassword = plainPassword

        return instance
    }

    static fromApiResponseData({id, firstName, lastName, email, currentToken}) {
        let instance = new User()

        instance.#id = id
        instance.#firstName = firstName
        instance.#lastName = lastName
        instance.#email = email
        instance.#currentToken = Token.fromApiResponseData(currentToken)

        return instance
    }

    getId() {
        return this.#id
    }

    getFirstName() {
        return this.#firstName
    }

    getLastName() {
        return this.#lastName
    }

    getEmail() {
        return this.#email
    }

    getCurrentToken() {
        return this.#currentToken
    }

    toJSON() {
        return {
            firstName: this.#firstName,
            lastName: this.#lastName,
            email: this.#email,
            plainPassword: this.#plainPassword,
        }
    }
}
