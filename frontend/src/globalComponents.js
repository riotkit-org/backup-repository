import BaseInput from './components/Inputs/BaseInput.vue'
import BaseCheckbox from './components/Inputs/BaseCheckbox.vue'  // @todo: Check if its used
import BaseRadio from './components/Inputs/BaseRadio.vue'        // @todo
import BaseDropdown from './components/BaseDropdown.vue'         // @todo
import Card from './components/Cards/Card.vue'

/**
 * You can register global components here and use them as a plugin in your main Vue instance
 */

const GlobalComponents = {
  install (Vue) {
    Vue.component(BaseInput.name, BaseInput)
    Vue.component(BaseCheckbox.name, BaseCheckbox)
    Vue.component(BaseRadio.name, BaseRadio)
    Vue.component(BaseDropdown.name, BaseDropdown)
    Vue.component('card', Card)
  }
}

export default GlobalComponents
