import ApiAuthenticationRequiredError from '../errors/ApiAuthenticationRequiredError.js'
import authentication from '../user/authentication.js'

export default async error => {
    if (error instanceof ApiAuthenticationRequiredError && !error.getOriginal().config?.disableAutoLogout) {
        await authentication.logout()
    }

    throw error
}
