<template>
   <span>
        <v-date-picker v-model="rangeSelector" is-range color="red">
            <template v-slot="{ inputValue, inputEvents }">
                <div class="flex justify-center items-center">
                    <input
                        :value="inputValue.start"
                        v-on="inputEvents.start"
                        class="border px-2 py-1 w-32 rounded focus:outline-none focus:border-indigo-300"
                    />
                    <svg
                        class="w-4 h-4 mx-2 date-panel-arrow"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                        />
                    </svg>
                    <input
                        :value="inputValue.end"
                        v-on="inputEvents.end"
                        class="border px-2 py-1 w-32 rounded focus:outline-none focus:border-indigo-300"
                    />
                </div>
            </template>
        </v-date-picker>
   </span>
</template>

<script>
import vDatePicker from 'v-calendar/lib/components/date-picker.umd'

export default {
    name: "date-picker",
    props: {
        range: {
            type: Object
        }
    },
    components: {
        vDatePicker
    },
    computed: {
        rangeSelector: {
            get: function () {
                return this.range
            },
            set: function (value) {
                this.$emit('range-changed', value)
            }
        }
    }
}
</script>

