import 'core-js'
import 'regenerator-runtime/runtime'

import {createApp} from 'vue'
import VueAxios from 'vue-axios'

import axios from './axios.js'
import App from './pages/App.vue'
import router from './router.js'
import store from './store/index.js'

createApp(App)
    .use(router)
    .use(VueAxios, axios)
    .use(store)
    .mount('#app')
