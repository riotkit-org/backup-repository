<template>
    <div class="wrapper">
        <side-bar>
            <!-- Normal menu -->
            <sidebar-link to="/admin/overview" v-if="isLoggedIn">
                <i class="bi bi-pie-chart-fill"></i>
                <p>Dashboard</p>
            </sidebar-link>
            <sidebar-link to="/admin/users" v-if="isLoggedIn">
                <i class="bi bi-people-fill"></i>
                <p>Users</p>
            </sidebar-link>
            <sidebar-link to="/admin/backup/collections" v-if="isLoggedIn">
                <i class="bi bi-file-earmark-arrow-up"></i>
                <p>Backup collections</p>
            </sidebar-link>

            <!-- Authentication -->
            <sidebar-link to="/admin/login" v-if="!isLoggedIn">
                <i class="bi bi-person-check-fill"></i>
                <p>Login</p>
            </sidebar-link>

            <sidebar-link to="/admin/logout" v-if="isLoggedIn">
                <i class="bi bi-door-closed"></i>
                <p>Logout</p>
            </sidebar-link>

        </side-bar>
        <div class="main-panel">
            <dashboard-content @click="toggleSidebar">
            </dashboard-content>
            <content-footer></content-footer>
        </div>
    </div>
</template>
<style lang="scss">

</style>
<script>
import ContentFooter from './ContentFooter.vue'
import DashboardContent from './Content.vue'

export default {
    components: {
        ContentFooter,
        DashboardContent
    },
    methods: {
        toggleSidebar() {
            if (this.$sidebar.showSidebar) {
                this.$sidebar.displaySidebar(false)
            }
        }
    },
    computed: {
        isLoggedIn: {
            get() {
                return this.$auth().isLoggedIn()
            },
            set(val) {}
        }
    }
}

</script>
