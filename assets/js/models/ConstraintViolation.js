export default class ConstraintViolation {
    property
    title

    constructor(property, title) {
        this.property = property
        this.title = title
    }

    getProperty() {
        return this.property
    }

    getTitle() {
        return this.title
    }
}
