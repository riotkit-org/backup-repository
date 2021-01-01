<template>
    <card v-if="user">
        <h4 slot="header" class="card-title" v-html="isAddingNew ? 'Create user profile' : 'Edit user profile'"></h4>
        <form>
            <div class="row" v-if="!isAddingNew">
                <div class="col-md-12">
                    <div class="alert alert-warning" role="alert">
                        Notice: Modifying fields related to security such as "Permissions", "Allowed User Agents" or "Allowed IP Addresses" can make your account locked and current session to be blocked
                        <strong>in case you are editing your own account</strong>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <base-input type="text"
                                label="Organization"
                                placeholder="Mining Workers Cooperative"
                                v-model="user.organization">
                    </base-input>
                </div>
                <div class="col-md-6">
                    <base-input type="email"
                                label="Email"
                                placeholder="someaddress@iwa-ait.org"
                                v-model="user.email"
                                :readonly="!isAddingNew">
                    </base-input>
                </div>
            </div>

            <div class="row" v-if="user.data">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tags (optional)</label>
                        <vue-tags-input
                            v-model="inputs.tag"
                            placeholder="Add new tag"
                            :tags="convertToTagsInput(user.data.tags)"
                            @tags-changed="newTags => user.data.tags = convertFromTagsInput(newTags)"
                        />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Allowed IP addresses (optional - allow list)</label>
                        <vue-tags-input
                            v-model="inputs.ipAddress"
                            placeholder="Insert new IPv4 address"
                            :tags="convertToTagsInput(user.data.allowedIpAddresses)"
                            @tags-changed="newTags => user.data.allowedIpAddresses = convertFromTagsInput(newTags)"
                        />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Allowed User Agents (optional - allow list)</label>
                        <vue-tags-input
                            v-model="inputs.userAgents"
                            placeholder="Append User Agent"
                            :tags="convertToTagsInput(user.data.allowedUserAgents)"
                            @tags-changed="newTags => user.data.allowedUserAgents = convertFromTagsInput(newTags)"
                        />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <permissions :available="availableRoles"
                                 :usable="usableRoles"
                                 title="Permissions"
                                 :selected="user.roles"
                                 :is-new="false"
                                 :roles-default-visibility="true"
                                 @selected="(permissionsToSet) => this.user.roles = permissionsToSet"
                    >

                        <template slot="toolbar-existing">&nbsp;</template>
                    </permissions>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>About Me</label>
                        <textarea rows="5" class="form-control border-input"
                                  placeholder="Describe user there"
                                  v-model="user.about">
                        </textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" v-if="!isAddingNew">
                    <base-input type="password"
                                label="Current password"
                                placeholder=""
                                v-model="inputs.currentPassword"
                    >
                    </base-input>
                </div>
                <div class="col-md-4">
                    <base-input type="password"
                                label="New password"
                                placeholder=""
                                v-model="inputs.password"
                    >
                    </base-input>
                </div>
                <div class="col-md-4">
                    <base-input type="password"
                                label="Repeat password"
                                placeholder=""
                                v-model="inputs.repeatPassword">
                    </base-input>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-danger btn-fill float-left" @click.prevent="revokeAccess" v-if="!isAddingNew">Revoke access</button>
                <button type="submit" class="btn btn-info btn-fill float-right" @click.prevent="updateProfile" v-if="!isAddingNew">Update Profile</button>
                <button type="submit" class="btn btn-info btn-fill float-right" @click.prevent="updateProfile" v-else>Add new user</button>
            </div>
            <div class="clearfix"></div>
        </form>
    </card>
</template>

<script>
import VueTagsInput from '@johmun/vue-tags-input';
import Card from 'src/components/Cards/Card.vue'
import Permissions from 'src/components/Security/Permissions.vue'
import {User} from 'src/models/auth.model.ts'

export default {
    components: {
        VueTagsInput,
        Card,
        Permissions
    },
    data() {
        return {
            isAddingNew: true,
            inputs: {
                tag: '',
                ipAddress: '',
                userAgents: '',

                currentPassword: '',
                password: '',
                repeatPassword: ''
            },
            selectedRoles: {
                'role_user': true
            },
            usableRoles: {},
            availableRoles: {},
            user: null
        }
    },
    methods: {
        /**
         * Fetch a User model from backend
         */
        fetchFromBackend() {
            let userId = this.$route.params.pathMatch
            let that = this

            // first find roles
            this.$authBackend().findPermissions('auth').then(function (roles) {
                that.usableRoles = roles.scoped
                that.availableRoles = roles.all

                // then find user
                if (that.isAddingNew) {
                    that.user = new User()
                } else {
                    that.$authBackend().findUserById(userId).then(function (user) {
                        that.user = user
                    })
                }
            })
        },

        updateProfile() {
            let hasError = false
            let that = this

            if (!this.user) {
                return
            }

            //
            // Pre-validate password typed in two fields
            // "Current password" is not validated for purpose - if the user is administrator then the field could be empty
            //
            if ((this.inputs.password.length || this.inputs.repeatPassword.length) && this.inputs.repeatPassword !== this.inputs.password) {
                this.notify('Passwords are not matching', 'bi bi-key')
                hasError = true
            }

            if (!this.user.email) {
                this.notify('E-mail is required', 'bi bi-envelope-open')
                hasError = true
            }

            if (!hasError) {
                this.$authBackend().saveUser(this.user, this.isAddingNew, this.inputs.currentPassword, this.inputs.password, this.inputs.repeatPassword)
                    .then(function (success) {
                        if (success) {
                            that.$notifications.notify({
                                message: 'User account saved',
                                icon: 'bi bi-person-plus-fill',
                                horizontalAlign: 'right',
                                verticalAlign: 'top',
                                type: 'success'
                            })
                        }
                    })
            }
        },

        revokeAccess() {
            let confirmation = prompt('Are you sure you want to revoke access for user ' + this.user.email + '?')

            if (confirmation) {

            }
        },

        notify(msg, icon = '') {
            this.$notifications.notify({
                message: msg,
                icon: icon,
                horizontalAlign: 'right',
                verticalAlign: 'top',
                type: 'danger'
            })
        },
        convertToTagsInput(tags) {
            let tagsInput = []

            for (let tag in tags) {
                tagsInput.push({'text': tags[tag]})
            }

            return tagsInput
        },

        convertFromTagsInput(tags) {
            return tags.map(function (tag) {
                return tag['text']
            })
        }
    },
    mounted() {
        this.isAddingNew = this.$route.params.pathMatch === undefined
        this.fetchFromBackend()
    }
}
</script>
<style>
    .checkbox {
        width: 25px;
    }

    .checkbox-label {
    }
</style>
