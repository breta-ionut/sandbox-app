export default class Credentials {
    /**
     * @type {string}
     */
    #username

    /**
     * @type {string}
     */
    #password

    /**
     * @returns {Credentials}
     */
    static fromViewData({username, password}) {
        const instance = new Credentials()

        instance.#username = username
        instance.#password = password

        return instance
    }

    /**
     * @returns {Object}
     */
    toJSON() {
        return {username: this.#username, password: this.#password}
    }
}
