import Vue from 'vue'
import VueRouter from 'vue-router'

import routes from './routes.js'
import App from './views/App.vue'

Vue.use(VueRouter)

new Vue({
    el: '#app',
    router: new VueRouter({mode: 'history', routes}),
    render: h => h(App)
})
