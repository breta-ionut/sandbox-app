import {createWebHistory, createRouter} from 'vue-router'

import Login from './components/Login.vue'

const routes = [
    {path: '/', name: 'login', component: Login},
]

export default createRouter({history: createWebHistory(), routes})
