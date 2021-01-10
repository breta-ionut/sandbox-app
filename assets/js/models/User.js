import Token from './Token.js'

export default class User {
    /**
     * @type {number}
     */
    #id

    /**
     * @type {string}
     */
    #firstName

    /**
     * @type {string}
     */
    #lastName

    /**
     * @type {string}
     */
    #email

    /**
     * @type {string}
     */
    #plainPassword

    /**
     * @type {Token}
     */
    #currentToken

    /**
     * @returns {User}
     */
    static fromViewData({firstName, lastName, email, plainPassword}) {
        let instance = new User()

        instance.#firstName = firstName
        instance.#lastName = lastName
        instance.#email = email
        instance.#plainPassword = plainPassword

        return instance
    }

    /**
     * @returns {User}
     */
    static fromApiResponseData({id, firstName, lastName, email, currentToken}) {
        let instance = new User()

        instance.#id = id
        instance.#firstName = firstName
        instance.#lastName = lastName
        instance.#email = email
        instance.#currentToken = Token.fromApiResponseData(currentToken)

        return instance
    }

    /**
     * @returns {number}
     */
    getId() {
        return this.#id
    }

    /**
     * @returns {string}
     */
    getFirstName() {
        return this.#firstName
    }

    /**
     * @returns {string}
     */
    getLastName() {
        return this.#lastName
    }

    /**
     * @returns {string}
     */
    getEmail() {
        return this.#email
    }

    /**
     * @returns {Token}
     */
    getCurrentToken() {
        return this.#currentToken
    }

    /**
     * @returns {Object}
     */
    toJSON() {
        return {
            firstName: this.#firstName,
            lastName: this.#lastName,
            email: this.#email,
            plainPassword: this.#plainPassword,
        }
    }
}
