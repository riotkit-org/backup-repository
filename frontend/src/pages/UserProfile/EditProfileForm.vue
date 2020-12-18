<template>
    <card>
        <h4 slot="header" class="card-title">Edit Profile</h4>
        <form>
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
                                v-model="user.email">
                    </base-input>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tags (optional)</label>
                        <vue-tags-input
                            v-model="inputs.tag"
                            placeholder="Add new tag"
                            :tags="user.data.tags"
                            @tags-changed="newTags => user.data.tags = newTags"
                        />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Allowed IP addresses (optional - allow list)</label>
                        <vue-tags-input
                            v-model="inputs.ipAddress"
                            placeholder="Insert new IPv4 address"
                            :tags="user.data.allowedIpAddresses"
                            @tags-changed="newTags => user.data.allowedIpAddresses = newTags"
                        />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Allowed User Agents (optional - allow list)</label>
                        <vue-tags-input
                            v-model="inputs.userAgents"
                            placeholder="Append User Agent"
                            :tags="user.data.allowedUserAgents"
                            @tags-changed="newTags => user.data.allowedUserAgents = newTags"
                        />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Permissions and roles</label>
                        <div v-for="(role, id) in roles">
                            <input type="checkbox" class="border-input checkbox" :value="id" :checked="selectedRoles[id]">
                            <label class="checkbox-label">{{ role }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>About Me</label>
                        <textarea rows="5" class="form-control border-input"
                                  placeholder="Describe user there"
                                  v-model="user.aboutMe">
                        </textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <base-input type="password"
                                label="New password"
                                placeholder=""
                                v-model="user.password"
                    >
                    </base-input>
                </div>
                <div class="col-md-6">
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

export default {
    components: {
        VueTagsInput, Card
    },
    data() {
        return {
            isAddingNew: true,
            inputs: {
                tag: '',
                ipAddress: '',
                userAgents: '',
                repeatPassword: ''
            },
            selectedRoles: {
                'role_user': true
            },
            roles: {
                'role_user': 'Can log in',
                'create_collections': 'Create backup collections'
            },
            user: {
                password: '',
                organization: 'Workers cooperative',
                email: 'mark@iwa-ait.org',
                aboutMe: '',
                data: {
                    tags: [],
                    maxAllowedFileSize: 0,
                    allowedIpAddresses: [],
                    allowedUserAgents: []
                }
            }
        }
    },
    methods: {
        updateProfile() {
            let hasError = false

            //
            // Pre-validate password typed in two fields
            //
            if ((this.user.password.length || this.inputs.repeatPassword.length) && this.inputs.repeatPassword !== this.user.password) {
                this.notify('Passwords are not matching', 'nc-lock-circle-open')
                hasError = true
            }

            if (!this.user.email) {
                this.notify('E-mail is required', 'nc-lock-circle-open')
                hasError = true
            }

            if (!hasError) {

            }
        },

        revokeAccess() {
            let confirmation = prompt('Are you sure you want to revoke access for user ' + this.user.email + '?')
        },

        notify(msg, icon = 'nc-lock-circle-open') {
            this.$notifications.notify({
                message: msg,
                icon: 'nc-icon ' + icon,
                horizontalAlign: 'right',
                verticalAlign: 'top',
                type: 'danger'
            })
        }
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
