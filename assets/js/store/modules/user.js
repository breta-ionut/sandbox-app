import userApi from '../../api/user.js'

export default {
    namespaced: true,

    state: () => ({
        user: null
    }),

    mutations: {
        setUser(state, user) {
            state.user = user
        }
    },

    actions: {
        login({commit}, credentials) {
            return userApi.login(credentials).then((user) => commit('setUser', user))
        }
    },
}
