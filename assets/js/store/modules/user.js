export default {
    namespaced: true,

    state: () => ({
        user: null,
    }),

    getters: {
        token: () => localStorage.user?.token,
        isTokenAvailable: (state, getters) => !!getters.token,
    },

    mutations: {
        set(state, user) {
            state.user = user

            localStorage.user = localStorage.user || {}
            localStorage.user.token = user.getCurrentToken().getToken()
        },
    },
}
