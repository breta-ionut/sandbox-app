export default class User {
    #id
    #firstName
    #lastName
    #email
    #plainPassword

    static fromViewData({firstName, lastName, email, plainPassword}) {
        let instance = new User()

        instance.#firstName = firstName
        instance.#lastName = lastName
        instance.#email = email
        instance.#plainPassword = plainPassword

        return instance
    }

    static fromApiResponseData({id, firstName, lastName, email}) {
        let instance = new User()

        instance.#id = id
        instance.#firstName = firstName
        instance.#lastName = lastName
        instance.#email = email

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

    toJSON() {
        return {
            firstName: this.#firstName,
            lastName: this.#lastName,
            email: this.#email,
            plainPassword: this.#plainPassword,
        }
    }
}
