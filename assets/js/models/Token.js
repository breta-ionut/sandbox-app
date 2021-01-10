export default class Token {
    #token
    #expiresAt

    static fromApiResponseData({token, expiresAt}) {
        let instance = new Token()

        instance.#token = token
        instance.#expiresAt = new Date(expiresAt)

        return instance
    }

    getToken() {
        return this.#token
    }

    getExpiresAt() {
        return this.#expiresAt
    }
}
