import BaseInput from './components/Inputs/BaseInput.vue'
import Card from './components/Cards/Card.vue'

/**
 * You can register global components here and use them as a plugin in your main Vue instance
 */

const GlobalComponents = {
  install (Vue) {
    Vue.component(BaseInput.name, BaseInput)
    Vue.component('card', Card)
  }
}

export default GlobalComponents
