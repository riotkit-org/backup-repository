<template>
    <div class="form-group"
         :class="{
          'input-group': hasIcon,
          'input-group-focus': focused
       }">
        <slot name="label">
            <label v-if="label" class="control-label">
                {{ label }}
            </label>
        </slot>
        <slot name="addonLeft">
      <span v-if="addonLeftIcon" class="input-group-prepend">
        <div class="input-group-text">
          <i :class="addonLeftIcon"></i>
        </div>
      </span>
        </slot>
        <slot>
            <input
                :data-input-id="inputId"
                :value="value"
                v-bind="$attrs"
                v-on="listeners"
                class="form-control"
                @blur="verifyElementChangeValue"
                aria-describedby="addon-right addon-left">
        </slot>
        <slot name="addonRight">
      <span v-if="addonRightIcon" class="input-group-append">
        <div class="input-group-text">
          <i :class="addonRightIcon"></i>
        </div>
      </span>
        </slot>
        <slot name="helperText"></slot>
    </div>
</template>
<script>
export default {
    inheritAttrs: false,
    name: "base-input",
    props: {
        label: {
            type: String,
            description: "Input label"
        },
        initialValue: {
            type: [String, Number],
            description: "Input value"
        },
        addonRightIcon: {
            type: String,
            description: "Input icon on the right"
        },
        addonLeftIcon: {
            type: String,
            description: "Input icon on the left"
        },
    },
    model: {
        prop: 'initialValue',
        event: 'input'
    },
    data() {
        return {
            focused: false,
            inputId: Math.random().toString(36).substring(7),
            value: ''
        }
    },
    computed: {
        hasIcon() {
            const {addonRight, addonLeft} = this.$slots;
            return addonRight !== undefined || addonLeft !== undefined || this.addonRightIcon !== undefined || this.addonLeftIcon !== undefined;
        },
        listeners() {
            return {
                ...this.$listeners,
                input: this.onInput,
                blur: this.onBlur,
                focus: this.onFocus
            }
        }
    },
    methods: {
        onInput(evt) {
            this.$emit('input', evt.target.value)
            this.value = evt.target.value
        },
        onFocus() {
            this.focused = true;
        },
        onBlur() {
            this.focused = false;
        },
        // https://github.com/vuejs/vue/issues/7058
        verifyElementChangeValue() {
            let domElement = document.querySelectorAll('[data-input-id="' + this.inputId + '"]')

            if (!domElement) {
                window.console.error('Cannot find input data-input-id=' + this.inputId)
            }

            if (domElement[0].value !== this.password) {
                this.value = domElement[0].value
                this.$emit('input', this.value)
            }
        }
    },
    mounted() {
        this.value = this.initialValue
    }
}
</script>
<style>

</style>
