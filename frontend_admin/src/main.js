import { createApp } from 'vue'
import './style.css'
import router from './router'
import App from './App.vue'
import { initTheme } from './composables/useTheme'

initTheme()

const app = createApp(App)

app.use(router)
app.mount('#app')
       