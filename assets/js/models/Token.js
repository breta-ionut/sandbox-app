export default class Token {
    /**
     * @type {string}
     */
    #token

    /**
     * @type {Date}
     */
    #expiresAt

    /**
     * @returns {Token}
     */
    static fromApiResponseData({token, expiresAt}) {
        const instance = new this()

        instance.#token = token
        instance.#expiresAt = new Date(expiresAt)

        return instance
    }

    /**
     * @returns {string}
     */
    getToken() {
        return this.#token
    }

    /**
     * @returns {Date}
     */
    getExpiresAt() {
        return this.#expiresAt
    }
}
