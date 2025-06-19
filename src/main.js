import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import api from './api'

const app = createApp(App)

// Add api as a global property
app.config.globalProperties.$api = api

app.use(router).mount('#app')
