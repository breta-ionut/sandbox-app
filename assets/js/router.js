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
    store.dispatch('user/getUser').finally(() => {
        let isRouteAnonymous = to.matched.some(route => route.meta?.anonymous)

        if (isRouteAnonymous && store.getters['user/isAuthenticated']) {
            next({name: 'home'})
        } else if (!isRouteAnonymous && !store.getters['user/isAuthenticated']) {
            next({name: 'login'})
        } else {
            next()
        }
    })
})

export default router
