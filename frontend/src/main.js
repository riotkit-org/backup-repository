/**
 * Backup Repository - administration frontend
 * ===========================================
 *   Highly secure, multi-tenant, docker ready, E2E encrypted backup complete solution
 *
 *   - Created by anarchist, grassroot, anti-capitalist, anti-authoritarian tech-collective "Riotkit"
 *   - Licensed under Apache 2.0
 */

import Vue from 'vue'
import VueRouter from 'vue-router'
import App from './App.vue'
import VTooltip from 'v-tooltip'
import LightBootstrap from './light-bootstrap-main'
import routes from './routes/routes'
import VModal from 'vue-js-modal'

Vue.use(VueRouter)
Vue.use(LightBootstrap)
Vue.use(VTooltip)
Vue.use(VModal)

import store from './plugins/store.ts'


const router = new VueRouter({
  routes,
  linkActiveClass: 'nav-item active',
  scrollBehavior: (to) => {
    if (to.hash) {
      return {selector: to.hash}
    } else {
      return { x: 0, y: 0 }
    }
  }
})

/* eslint-disable no-new */
new Vue({
  el: '#app',
  render: h => h(App),
  router,
  store
})
