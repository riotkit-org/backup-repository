<!--
  -- Widget that renders a list of checkboxes from list of available options, and checks them basing on second list - list of selected options
  -- The widget does not communicate with backend
  -->

<template>
    <div class="form-group">
        <label class="clickable-label" v-html="title" @click="(value) => this.isVisible = !this.isVisible"></label> <small> ({{selectedRoles.length}})</small>
        <slot name="toolbar">
            <slot name="toolbar-toggle">
                <i class="bi-wrench clickable-label toolbar" @click="(value) => this.isVisible = !this.isVisible" v-tooltip.top-center="'Show/hide roles list assigned to this user access'"></i>
            </slot>
            <i class="bi-layers-half clickable-label toolbar" @click="onShowAdvancedClicked" v-tooltip.top-center="'Toggle role names/description'"></i>
            <slot name="toolbar-existing" v-if="!isNew">
                <i class="bi-trash clickable-label toolbar" v-tooltip.top-center="'Revoke access'" @click="onAccessRevoking"></i>
            </slot>
            <slot name="toolbar-new" v-if="isNew">
                <i class="bi bi-person-plus-fill clickable-label toolbar" @click="onNewUserAccessAdding" v-tooltip.top-center="'Submit new access to this collection'"></i>
            </slot>
        </slot>

        <slot name="selector">
            <user-selector v-if="isNew" @selected="(user) => onUserSelected(user)" :style="isVisible ? '' : 'display: none;'"/>
        </slot>

        <div v-for="role in available.permissions" v-if="available" :style="isVisible ? '' : 'display: none;'">
            <input type="checkbox" class="border-input checkbox" :checked="isChecked(role.id)" @change="onCheckboxChange(role.id)" :disabled="isDisabled(role.id)">
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
        /**
         * All available roles. All will be listed as checkboxes
         */
        available: {
            default() {
                return {}
            }
        },
        /**
         * List of roles that are actually SELECTABLE, the rest will be grayed out.
         * This input is OPTIONAL.
         */
        usable: {
            default() {
                return []
            }
        },
        /**
         * Preselected roles list
         */
        selected: {
            default() {
                return []
            }
        },
        /**
         * Is this widget about adding new user access to some object?
         */
        isNew: {
            default: false
        },
        /**
         * User ID
         */
        uid: {
            default: null
        },
        /**
         * Should the list of roles be by default expanded?
         */
        rolesDefaultVisibility: {
            default: false
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
        this.isVisible = this.rolesDefaultVisibility
        this.selectedRoles = this.selected
        this.userId = this.uid
    },
    methods: {
        isChecked(permissionId) {
            if (!this.selectedRoles) {
                return false
            }

            return this.selectedRoles.includes(permissionId)
        },

        isDisabled(permissionId) {
            // usable roles property is optional
            if (!this.usable) {
                return false
            }

            return !this.usable.includes(permissionId)
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
