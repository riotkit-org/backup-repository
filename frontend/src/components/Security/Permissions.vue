<!--
  -- Widget that renders a list of checkboxes from list of available options, and checks them basing on second list - list of selected options
  -- The widget does not communicate with backend
  -->

<template>
    <div class="form-group">
        <label class="clickable-label" v-html="title" @click="(value) => isVisible = !isVisible"></label> <small> ({{selectedRoles.length}})</small>
        <svg @click="(value) => isVisible = !isVisible" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="clickable-label w-4 h-4 mx-2 date-panel-arrow"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        <div v-for="role in available.permissions" v-if="available" :style="isVisible ? '' : 'display: none;'">
            <input type="checkbox" class="border-input checkbox" :checked="isChecked(role.id)" @change="onCheckboxChange(role.id)">
            <label class="checkbox-label role-description" v-html="role.description && !showAdvanced ? role.description : role.id"></label>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            default: 'Permissions and roles'
        },
        limits: {
            default: ''
        },
        available: {
            default() {
                return {}
            }
        },
        selected: {
            default() {
                return []
            }
        }
    },
    data() {
        return {
            isVisible: false,
            showAdvanced: true,
            selectedRoles: []
        }
    },
    mounted() {
        this.selectedRoles = this.selected
    },
    methods: {
        isChecked(role) {
            if (!this.selectedRoles) {
                return false
            }

            return this.selectedRoles.includes(role)
        },

        onCheckboxChange(role) {
            if (this.isChecked(role)) {
                this.selectedRoles = this.selectedRoles.filter(function(value) {
                    return value !== role;
                });
            } else {
                this.selectedRoles.push(role)
            }

            this.$emit('selected', this.selectedRoles)
        }
    }
}
</script>

<style>
.role-description {
    font-size: 10px !important;
    white-space: initial;
    word-wrap: break-word;
}

.clickable-label {
    cursor: pointer;
}
</style>
