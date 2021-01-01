<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <card class="card-plain">
                        <template slot="header">
                            <h4 class="card-title">Users</h4>
                            <p class="card-category">Individual people or organization accounts can have assigned various levels of access basing on roles and attributes</p>
                        </template>

                        <template slot="filters">
                            <div id="filter-panel" class="filter-panel">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <form class="form-inline" role="form">
                                            <div class="form-group">
                                                <label class="filter-col">Search:</label>
                                                <input type="text" @change="() => this.fetchFromBackend()" v-model="searchPhrase" class="form-control input-sm">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="table-responsive">
                            <l-table class="table-hover"
                                     :columns="users.columns"
                                     :data="users.data">
                            </l-table>
                        </div>
                    </card>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <v-pagination v-model="currentPage" :page-count="maxPages" :classes="bootstrapPaginationClasses"></v-pagination>
                </div>

                <div class="col-6">
                    <div class="text-center">
                        <button type="submit" class="btn btn-fill float-right" @click.prevent="addNewUser">Add user</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LTable from 'src/components/Table.vue'
import Card from 'src/components/Cards/Card.vue'
import vPagination from 'vue-plain-pagination'

export default {
    components: {
        LTable,
        Card,
        vPagination
    },
    data () {
        return {
            users: {
                columns: ['Id', 'Email', 'Organization', 'Administrator'],
                data: []
            },
            searchPhrase: '',
            currentPage: 1,
            maxPages: 1,
            limitPerPage: 20,
            bootstrapPaginationClasses: {
                ul: 'pagination',
                li: 'page-item',
                liActive: 'active',
                liDisable: 'disabled',
                button: 'page-link'
            }
        }
    },
    methods: {
        addNewUser() {
            window.location.href = '#/admin/user'
        },
        fetchFromBackend() {
            let that = this

            this.$authBackend().findUsers(this.searchPhrase, this.currentPage, this.limitPerPage).then(function (response) {
                that.users.data = []

                // update pagination
                that.maxPages = response.pagination.max

                for (let userNum in response.users) {
                    let user = response.users[userNum]

                    that.users.data.push({
                        id: user.id,
                        email: user.email,
                        organization: user.organization,
                        administrator: user.isAdmin ? 'Yes' : 'No',
                        _url: '#/admin/user/' + user.id,
                        _active: true
                    })
                }
            })
        }
    },
    mounted() {
        this.fetchFromBackend()
    },
    watch: {
        'currentPage': function () {
            this.fetchFromBackend()
        }
    }
}
</script>
