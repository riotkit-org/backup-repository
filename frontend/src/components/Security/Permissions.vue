<!--
  -- Widget that renders a list of checkboxes from list of available options, and checks them basing on second list - list of selected options
  -- The widget does not communicate with backend
  -->

<template>
    <div class="form-group">
        <label class="clickable-label" v-html="title" @click="(value) => isVisible = !isVisible"></label> <small> ({{selectedRoles.length}})</small>
        <slot name="toolbar">
            <i class="bi-wrench clickable-label toolbar" @click="(value) => isVisible = !isVisible" v-tooltip.top-center="'Show/hide roles list assigned to this user access'"></i>
            <i class="bi-layers-half clickable-label toolbar" @click="onShowAdvancedClicked" v-tooltip.top-center="'Toggle role names/description'"></i>
            <slot name="toolbar-existing" v-if="!isNew">
                <i class="bi-trash clickable-label toolbar" v-tooltip.top-center="'Revoke access'" @click="onAccessRevoking"></i>
            </slot>
            <slot name="toolbar-new" v-if="isNew">
                <i class="bi bi-person-plus-fill clickable-label toolbar" @click="onNewUserAccessAdding" v-tooltip.top-center="'Submit new access to this collection'"></i>
            </slot>
        </slot>

        <user-selector v-if="isNew" @selected="(user) => onUserSelected(user)" :style="isVisible ? '' : 'display: none;'"/>

        <div v-for="role in available.permissions" v-if="available" :style="isVisible ? '' : 'display: none;'">
            <input type="checkbox" class="border-input checkbox" :checked="isChecked(role.id)" @change="onCheckboxChange(role.id)">
            <label class="checkbox-label role-description" v-html="role.description && !showAdvanced ? role.description : role.id"></label>
        </div>
    </div>
</template>

<script>
import UserSelector from './UserSelector.vue'

export default {
    components: {
        UserSelector
    },
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
        },
        isNew: {
            default: false
        },
        creationResult: {
            default: null
        },
        uid: {
            default: null
        }
    },
    data() {
        return {
            isVisible: false,
            showAdvanced: false,
            selectedRoles: []
        }
    },
    mounted() {
        this.selectedRoles = this.selected
        this.userId = this.uid
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

            if (!this.isNew) {
              this.$emit('selected', this.selectedRoles)
            }
        },

        onShowAdvancedClicked() {
            this.showAdvanced = !this.showAdvanced
            this.isVisible = true
        },

        /**
         * When a new access is assigned
         */
        onNewUserAccessAdding() {
            this.$emit('newAccessSubmitted', this.selectedRoles, this.userId)

            if (this.creationResult === true) {
                // then clean up
                this.selectedRoles = []
                this.isVisible = false
                this.showAdvanced = false
            }
        },

        onUserSelected(userId) {
            this.userId = userId
        },

        onAccessRevoking() {
            this.$emit('access-revoked', this.userId)
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
.toolbar {
    margin-left: 10px;
}
</style>
