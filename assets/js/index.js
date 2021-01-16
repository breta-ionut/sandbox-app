import 'core-js'
import 'regenerator-runtime/runtime'

import {createApp} from 'vue'
import VueAxios from 'vue-axios'

import router from './router.js'
import axios from './axios.js'
import store from './store/index.js'
import App from './components/App.vue'

createApp(App)
    .use(router)
    .use(VueAxios, axios)
    .use(store)
    .mount('#app')
