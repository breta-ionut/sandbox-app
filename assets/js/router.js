import {createWebHistory, createRouter} from 'vue-router'

import Login from './components/Login.vue'
import store from './store/index.js'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {path: '/login', name: 'login', component: Login, meta: {anonymous: true}},
        {path: '/', name: 'home'},
    ],
})

router.beforeEach((to, from, next) => {
    let isRouteAnonymous

    if (!store.state['user/userLoading'] && !store.state['user/userLoaded']) {
        store.dispatch('user/getUser')
            .then(() => next())
            .catch(() => next(false))

        return
    }

    isRouteAnonymous = to.matched.some(route => route.meta?.anonymous)

    if (isRouteAnonymous && store.getters['user/isAuthenticated']) {
        next({name: 'home'})
    } else if (!isRouteAnonymous && !store.getters['user/isAuthenticated']) {
        next({name: 'login'})
    } else {
        next()
    }
})

export default router
