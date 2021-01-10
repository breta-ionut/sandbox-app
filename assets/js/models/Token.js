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
        let instance = new Token()

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
