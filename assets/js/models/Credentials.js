export default class Credentials {
    #username
    #password

    static fromViewData({username, password}) {
        const instance = new Credentials()

        instance.#username = username
        instance.#password = password

        return instance
    }

    toJSON() {
        return {username: this.#username, password: this.#password}
    }
}
