import 'core-js'
import 'regenerator-runtime/runtime'

import {createApp} from 'vue'
import {createRouter, createWebHistory} from 'vue-router'
import VueAxios from 'vue-axios'

import routes from './routes.js'
import axios from './axios.js'
import store from './store/index.js'
import App from './components/App.vue'

const router = createRouter({history: createWebHistory(), routes})

createApp(App)
    .use(router)
    .use(VueAxios, axios)
    .use(store)
    .mount('#app')
