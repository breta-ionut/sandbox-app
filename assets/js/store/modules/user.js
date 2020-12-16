import userApi from '../../api/user.js'

export default {
    namespaced: true,

    state: () => ({
        user: null,
    }),

    mutations: {
        set(state, user) {
            state.user = user
        },
    },

    actions: {
        async get({commit}) {
            return userApi.get().then((user) => commit('set', user))
        },
    },
}
