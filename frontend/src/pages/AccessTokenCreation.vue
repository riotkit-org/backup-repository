<template>
    <div>
        <div v-if="generatedToken" class="generated-token">
            <h3>Access token generated</h3>
            <div class="token-notice">
                Please copy it right now. You will not be able to copy it later.
                <strong>Don't give this token anybody</strong>, it's a secret key to access Backup Repository as your account with a permissions you selected.<br/>
                Use this token to manage your account, and/or send backups programmatically using a backup client such as Bahub.
            </div>
            <textarea class="form-control" readonly v-model="generatedToken" rows="20"></textarea>
        </div>

        <permissions
            v-if="!generatedToken"
            title="Generate a new API access token"
            :selected="[]"
            :usable="usable"
            :available="allPermissions"
            :roles-default-visibility="true"
            :is-new="false"
            @selected="(elements) => this.selected = elements">
            <template slot="toolbar-toggle">&nbsp;</template>
            <template slot="toolbar-existing">
                <i class="bi bi-plus-circle-fill clickable-label toolbar" data-field="Create access token" v-tooltip.top-center="'Create access token'" @click="submitNew"></i>
            </template>

            <template slot="selector">
                <select name="ttl" class="form-control" v-model="ttl" data-field="Select how long the token should be valid">
                    <option value="0">Select how long the token should be valid</option>
                    <option v-for="(value, label) in ttlSelectorValues" :value="value" v-html="label"/>
                </select>
                <div class="token-description">
                    <textarea class="form-control"
                              v-model="selectedDescription"
                              rows="5"
                              name="description"
                              placeholder="Describe the purpose of this token. How and when it will be used? Description can help you having a clear look on all usages of your account."
                    />
                </div>
            </template>
        </permissions>
    </div>
</template>

<script>
import Permissions from "src/components/Security/Permissions.vue";

export default {
    components: {
        Permissions
    },
    data() {
        return {
            allPermissions: {},
            usable: {},
            selected: [],
            ttl: 0,
            selectedDescription: '',
            ttlSelectorValues: {},
            generatedToken: ''
        }
    },
    methods: {
        fetchFromBackend() {
            let that = this

            this.$authBackend().findPermissions('auth').then(function (permissions) {
                that.allPermissions = permissions.all
                that.usable = permissions.scoped
            })
        },

        populateTTLSelectorValues() {
            for (let hour = 1; hour <= 24; hour++) {
                this.ttlSelectorValues['+' + hour + 'h'] = hour * 3600
            }

            for (let day = 1; day <= 90; day++) {
                this.ttlSelectorValues['+' + day + ' days'] = day * 86400
            }

            for (let month = 1; month <= 120; month++) {
                this.ttlSelectorValues['+' + month + ' months'] = month * 86400 * 24
            }
        },

        submitNew() {
            let that = this

            if (!this.ttl) {
                that.$notifications.notify({
                    message: '"Time To Live" must be selected. At least 1 hour',
                    horizontalAlign: 'right',
                    verticalAlign: 'top',
                    type: 'danger'
                })

                return false
            }

            this.$authBackend().createAccessToken(this.selected, this.ttl, this.selectedDescription).then(function (token) {
                if (token) {
                    that.generatedToken = token
                    that.$notifications.notify({
                        message: 'Token "' + token + '" generated',
                        horizontalAlign: 'right',
                        verticalAlign: 'top',
                        type: 'success'
                    })

                    that.$emit('token-created')
                }
            })
        }
    },
    mounted() {
        this.populateTTLSelectorValues()
        this.fetchFromBackend()
    }
}
</script>

<style>
.generated-token {
    margin-left: 15px;
    margin-right: 15px;
}

.token-notice {
    margin-bottom: 15px;
}

.token-description {
    margin-top: 15px;
    margin-bottom: 15px;
}
</style>
