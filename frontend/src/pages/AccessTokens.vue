<template>
    <div class="content">
        <!-- Creation modal -->
        <modal name="grant-new-permission"
               width="80%"
               height="80%"
               :reset="true"
        >
            <access-token-creation @token-created="onTokenCreated"/>
        </modal>

        <!-- Edit modals -->
        <modal
            v-for="(item, index) in accessTokens.data"
            :name="'modal-' + item.tokenHash"
            width="60%"
            height="60%"
            :reset="true"
        >
            <permissions :selected="item.permissions" :available="allPermissions" :usable="['none']" :roles-default-visibility="true">
                <template slot="toolbar-existing">&nbsp;</template>
            </permissions>
        </modal>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <card class="card-plain">
                        <template slot="header">
                            <div class="row">
                                <div class="col-10">
                                    <h4 class="card-title">Granted access</h4>
                                    <p class="card-category">
                                        Manage active browser sessions as well as long term API tokens
                                    </p>
                                </div>

                                <div class="col-2">
                                    <button type="button" class="btn btn-default btn-small" @click="showTokenCreationModal">
                                        <span class="bi bi-key" aria-hidden="true"></span> Grant a new access
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th v-for="column in accessTokens.columns" :key="column">{{column}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in accessTokens.data" :key="index" :class="item.isValid() ? 'active' : 'inactive'">
                                        <td>
                                            <small>{{ item.generatedAt.getFormattedDate() }} - {{ item.expiration.getFormattedDate() }}</small>
                                        </td>
                                        <td>
                                            {{ item.getTTL() }}
                                        </td>
                                        <td>
                                            <input type="text" readonly :value="item.tokenHash" class="form-control" style="width: 300px;">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-default btn-small" @click="() => showPermissions(item.tokenHash)">
                                                <span class="bi bi-layers-half" aria-hidden="true"></span> Show
                                            </button>
                                        </td>
                                        <td>
                                            <input type="text" readonly :value="item.user" class="form-control" style="width: 300px;">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-default btn-small" :disabled="!item.isValid()" @click="() => revokeAccess(item.tokenHash)">
                                                <span class="bi bi-key" aria-hidden="true"></span> Revoke
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-if="!accessTokens.data || !accessTokens.data.length">
                                    <tr>
                                        <td>
                                            No data to display
                                        </td>
                                        <td v-for="column in accessTokens.columns" :key="column"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </card>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <v-pagination v-model="currentPage" :page-count="maxPages" :classes="bootstrapPaginationClasses"></v-pagination>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LTable from 'src/components/Table.vue'
import Card from 'src/components/Cards/Card.vue'
import Permissions from "src/components/Security/Permissions.vue";
import vPagination from 'vue-plain-pagination'
import AccessTokenCreation from "@/pages/AccessTokenCreation";

export default {
    components: {
        AccessTokenCreation,
        LTable,
        Card,
        vPagination,
        Permissions
    },
    data() {
        return {
            accessTokens: {
                columns: ['Valid', 'TTL', 'Identifier Hash', 'Permissions', 'User ID', 'Actions'],
                data: {}
            },
            currentPage: 1,
            maxPages: 1,
            bootstrapPaginationClasses: {
                ul: 'pagination',
                li: 'page-item',
                liActive: 'active',
                liDisable: 'disabled',
                button: 'page-link'
            },
            allPermissions: {}
        }
    },
    methods: {
        fetchFromBackend() {
            let that = this

            this.$authBackend().findPermissions().then(function (permissions) {
                that.allPermissions = permissions.all
            })

            this.$authBackend().findAccessTokens(this.currentPage).then(function (response) {
                that.accessTokens.data = []
                that.$nextTick(function() {
                    that.accessTokens.data = response.accessList
                    that.maxPages = response.pagination.max
                })
            })
        },

        revokeAccess(tokenHash) {
            let that = this

            this.$authBackend().revokeToken(tokenHash).then(function (status) {
                if (status === true) {
                    that.$notifications.notify({
                        message: 'Access revoked',
                        horizontalAlign: 'right',
                        verticalAlign: 'top',
                        type: 'success'
                    })

                    // refresh the list
                    that.fetchFromBackend()
                }
            })
        },

        showPermissions(tokenHash) {
            this.$modal.show('modal-' + tokenHash)
        },

        showTokenCreationModal() {
            this.$modal.show('grant-new-permission')
        },

        onTokenCreated() {
            this.fetchFromBackend()
        }
    },
    mounted: function () {
        this.fetchFromBackend()
    },
    watch: {
        'currentPage': function () {
            this.fetchFromBackend()
        }
    }
}
</script>
<style>
.inactive {
    color: #9A9A9A !important;
}

.active a, .active a:hover {
    color: black;
}

div[role=dialog] {
    padding: 15px;
    overflow: auto;
}
</style>
