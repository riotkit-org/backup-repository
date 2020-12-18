<template>
    <div class="wrapper">
        <side-bar>
            <mobile-menu slot="content"></mobile-menu>

            <!-- Normal menu -->
            <sidebar-link to="/admin/overview" v-if="isLoggedIn">
                <i class="nc-icon nc-chart-pie-35"></i>
                <p>Dashboard</p>
            </sidebar-link>
            <sidebar-link to="/admin/users" v-if="isLoggedIn">
                <i class="nc-icon nc-circle-09"></i>
                <p>Users</p>
            </sidebar-link>
            <sidebar-link to="/admin/backup-collections" v-if="isLoggedIn">
                <i class="nc-icon nc-single-copy-04"></i>
                <p>Backup collections</p>
            </sidebar-link>

            <!-- Authentication -->
            <sidebar-link to="/admin/login" v-if="!isLoggedIn">
                <i class="nc-icon nc-circle-09"></i>
                <p>Login</p>
            </sidebar-link>

            <sidebar-link to="/admin/logout" v-if="isLoggedIn">
                <i class="nc-icon nc-circle-09"></i>
                <p>Logout</p>
            </sidebar-link>


            <!--      <sidebar-link to="/admin/icons">-->
            <!--        <i class="nc-icon nc-atom"></i>-->
            <!--        <p>Icons</p>-->
            <!--      </sidebar-link>-->
            <!--      <sidebar-link to="/admin/maps">-->
            <!--        <i class="nc-icon nc-pin-3"></i>-->
            <!--        <p>Maps</p>-->
            <!--      </sidebar-link>-->
            <!--      <sidebar-link to="/admin/notifications">-->
            <!--        <i class="nc-icon nc-bell-55"></i>-->
            <!--        <p>Notifications</p>-->
            <!--      </sidebar-link>-->

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
import MobileMenu from './MobileMenu.vue'

export default {
    components: {
        ContentFooter,
        DashboardContent,
        MobileMenu
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
