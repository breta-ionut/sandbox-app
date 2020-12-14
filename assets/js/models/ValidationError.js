import Error from './Error.js';

export class ValidationError extends Error {
    violations = []

    constructor(title, code, detail, violations) {
        super(title, code, detail);

        this.violations = violations
    }

    getViolations() {
        return this.violations
    }
}
