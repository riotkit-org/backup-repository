<template>
    <card>
        <template slot="header">
            <h4 class="card-title">
                Edit permissions
            </h4>
            <p class="card-category">
                Assign specific permissions to given users in context of this Backup Collection
            </p>
        </template>

        <div class="row">
            <div class="col-md-12">
                <permissions :available="availablePermissions"
                             :usable="usablePermissions"
                             v-if="showCreationForm"
                             title="<b>Grant a new access</b>"
                             isNew="true"
                             @newAccessSubmitted="(permissions, userId) => createUserAccess(permissions, userId)"
                />

                <permissions v-for="user in users"
                             :available="availablePermissions"
                             :usable="usablePermissions"
                             :selected="user.roles"
                             @selected="(permissions) => updatePermissions(permissions, user.userId)"
                             @access-revoked="(userId) => revokeAccess(userId)"
                             :title="user.userEmail"
                             :v-html="user"
                             :uid="user.userId"
                />
            </div>
        </div>
    </card>
</template>

<script>
import Permissions from "src/components/Security/Permissions.vue";

export default {
    components: {
        Permissions
    },
    props: {
        collection: {}
    },
    data: function () {
        return {
            availablePermissions: {},
            usablePermissions: {},
            users: [],
            showCreationForm: true
        }
    },
    mounted() {
        this.refreshView()
    },
    methods: {
        /**
         * Updates permissions of a user that already has access to the collection
         */
        updatePermissions(roles, userId) {
            let that = this

            this.debounce('roles:' + userId, function () {
                for (let accessNum in that.users) {
                    let user = that.users[accessNum]

                    if (user && user.userId === userId) {
                        that.$backupCollectionBackend().createOrUpdateUserAccessForCollection(that.collection, userId, roles).then(function (result) {
                            if (result) {
                                that.$notifications.notify({
                                    message: 'Permissions for user ' + user.userEmail + ' saved',
                                    icon: 'nc-icon',
                                    horizontalAlign: 'right',
                                    verticalAlign: 'top',
                                    type: 'success'
                                })
                            }
                        })
                        break
                    }
                }
            }, 800)()
        },

        /**
         * Assigns a new user an access to the collection
         *
         * @param roles
         * @param userId
         */
        createUserAccess(roles, userId) {
            let that = this

            that.$backupCollectionBackend().createOrUpdateUserAccessForCollection(that.collection, userId, roles).then(function (result) {
                if (result) {
                    that.$notifications.notify({
                        message: 'Permissions for user created',
                        icon: 'nc-icon',
                        horizontalAlign: 'right',
                        verticalAlign: 'top',
                        type: 'success'
                    })

                    that.refreshAccessList()
                    that.showCreationForm = false

                    that.$nextTick(function () {
                        that.showCreationForm = true
                    })
                }
            })
        },

        revokeAccess(userId) {
            let that = this

            this.$backupCollectionBackend().revokeUserAccessInCollection(this.collection, userId).then(function (status) {
                if (status) {
                    that.$notifications.notify({
                        message: 'Access for user was revoked',
                        icon: 'nc-icon',
                        horizontalAlign: 'right',
                        verticalAlign: 'top',
                        type: 'warning'
                    })

                    that.refreshAccessList()
                }
            })
        },

        /**
         * Reload the whole list
         */
        refreshAccessList() {
            this.users = []

            let that = this

            this.$backupCollectionBackend().findAuthorizedUsersAndRoles(this.collection).then(function (users) {
                that.users = users
            })
        },

        refreshView() {
            if (!this.collection.id || !this.collection) {
                return
            }

            let that = this

            this.$authBackend().findPermissions('auth,collection').then(function (permissions) {
                that.availablePermissions = permissions.all
                that.usablePermissions = permissions.scoped
            })

            this.refreshAccessList()
        }
    },
    watch: {
        'collection.id': function () {
            this.refreshView()
        },

        'collection': function () {
            this.refreshView()
        }
    }
}
</script>
