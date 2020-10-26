import {createApp} from 'vue'
import {createRouter, createWebHistory} from 'vue-router'

import routes from './routes.js'
import App from './views/App.vue'

const router = createRouter({history: createWebHistory(), routes})

createApp(App)
    .use(router)
    .mount('#app')
