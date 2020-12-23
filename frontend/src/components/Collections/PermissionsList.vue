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
                <permissions v-for="user in users"
                             :available="availableRoles"
                             :selected="user.roles"
                             @selected="(permissions) => updatePermissions(permissions, user.userId)"
                             :title="user.userEmail"
                             :v-html="user"
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
            availableRoles: {},
            users: []
        }
    },
    methods: {
        updatePermissions(roles, userId) {
            let that = this

            this.debounce('roles:' + userId, function () {
                for (let accessNum in that.users) {
                    let user = that.users[accessNum]

                    if (user.userId === userId) {
                        that.$authBackend().updateRolesForCollection(that.collection, userId, roles)
                        break
                    }
                }
            }, 800)()
        }
    },
    watch: {
        'collection.id': function () {
            if (!this.collection.id) {
                return
            }

            let that = this

            this.$authBackend().findRoles('auth,collection').then(function (roles) {
                that.availableRoles = roles
            })

            this.$backupCollectionBackend().findAuthorizedUsersAndRoles(this.collection).then(function (users) {
                that.users = users
            })
        }
    }
}
</script>
