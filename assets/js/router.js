import {createWebHistory, createRouter} from 'vue-router'

import Dashboard from './components/Dashboard.vue'
import Login from './components/Login.vue'
import authentication from './user/authentication.js'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {path: '/login', name: 'login', component: Login, meta: {anonymous: true}},
        {path: '/', name: 'dashboard', component: Dashboard},
    ],
})

router.beforeEach((to, from, next) => {
    authentication.loadUser().then(() => {
        let isRouteAnonymous = to.matched.some(route => route.meta?.anonymous)

        if (isRouteAnonymous && authentication.isAuthenticated()) {
            next({name: 'dashboard'})
        } else if (!isRouteAnonymous && !authentication.isAuthenticated()) {
            next({name: 'login'})
        } else {
            next()
        }
    })
})

export default router
