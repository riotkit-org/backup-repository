<template>
    <footer class="footer">
        <div class="container-fluid">
            <div class="footer-menu" v-if="appInfo != null">
                <span v-if="appInfo">
                    <a href="https://github.com/riotkit-org/backup-repository" target="_blank">Backup Repository</a> {{ appInfo.version }} running on {{ appInfo.dbType }}
                </span>
            </div>

            <div class="copyright text-center">
                Frontend based on Open Source work of <a href="https://binarcode.com" target="_blank">BinarCode</a> and <a href="https://www.creative-tim.com/?ref=pdf-vuejs" target="_blank">Creative Tim</a>
            </div>
        </div>
    </footer>
</template>
<script>
import { mapState } from 'vuex';

export default {
    data() {
        return {
            appInfo: null
        }
    },
    computed: {
        ...mapState(['userJwt']),
    },
    created() {
        this.refreshVersionInformation()
    },
    watch: {
        userJwt(val) {
            if (val) {
                this.refreshVersionInformation()
            } else {
                this.appInfo = null
            }
        }
    },
    methods: {
        refreshVersionInformation() {
            let that = this

            this.$backend().getApplicationInfo().then(function(returnedAppInfo) {
                that.appInfo = returnedAppInfo
            })
        }
    }
}
</script>
<style>

</style>
