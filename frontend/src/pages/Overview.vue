<template>
    <div class="content">
        <div class="container-fluid">

            <!--
              -- Stats cards
              -->
            <div class="row" v-if="metricsFetched">
                <div class="col-xl-3 col-md-6" v-if="metrics.storage.used_space !== null && metrics.storage.declared_space !== null">
                    <stats-card>
                        <div slot="header" class="icon-warning">
                            <i class="bi bi-pie-chart-fill text-warning"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Used/Declared disk space</p>
                            <h5 class="card-title">
                                {{ metrics.storage.used_space }}/{{ metrics.storage.declared_space }}
                            </h5>
                        </div>
                    </stats-card>
                </div>

                <div class="col-xl-3 col-md-6" v-if="metrics.users.active_accounts !== null">
                    <stats-card>
                        <div slot="header" class="icon-success">
                            <i class="bi bi-people-fill text-success"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Active User accounts</p>
                            <h4 class="card-title">{{ metrics.users.active_accounts }}</h4>
                        </div>
                    </stats-card>
                </div>

                <div class="col-xl-3 col-md-6" v-if="metrics.users.active_jwt_keys !== null">
                    <stats-card>
                        <div slot="header" class="icon-success">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Authorized JWT keys</p>
                            <h4 class="card-title">{{ metrics.users.active_jwt_keys }}</h4>
                        </div>
                    </stats-card>
                </div>

                <div class="col-xl-3 col-md-6" v-if="metrics.backup.versions !== null">
                    <stats-card>
                        <div slot="header" class="icon-danger">
                            <i class="bi bi-files text-danger"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Active versions</p>
                            <h4 class="card-title">{{ metrics.backup.versions }}</h4>
                        </div>
                    </stats-card>
                </div>

                <div class="col-xl-3 col-md-6" v-if="metrics.backup.collections !== null">
                    <stats-card>
                        <div slot="header" class="icon-info">
                            <i class="bi bi-file-earmark-arrow-up text-primary"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Backup collections</p>
                            <h4 class="card-title">{{ metrics.backup.collections }}</h4>
                        </div>
                    </stats-card>
                </div>

                <div class="col-xl-3 col-md-6" v-if="metrics.resources.tags !== null">
                    <stats-card>
                        <div slot="header" class="icon-info">
                            <i class="bi bi-geo text-danger"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Resource tags</p>
                            <h4 class="card-title">{{ metrics.resources.tags }}</h4>
                        </div>
                    </stats-card>
                </div>
            </div>

            <!--
              -- Bigger panels with stats - charts and tables
              -->
            <div class="row">
                <div class="col-md-12">
                    <card>
                        <template slot="header">
                            <h4 class="card-title">Recently submitted versions</h4>
                        </template>
                        <l-table :data="backupsList.data"
                                 :columns="[]">
                            <template slot="columns"></template>
                            <template slot-scope="{row}">
                                <td>{{ row.title }} (v{{ row.version }} at {{ row.creationDate.getFormattedDate() }})</td>
                            </template>
                        </l-table>
                    </card>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ChartCard from 'src/components/Cards/ChartCard.vue'
import StatsCard from 'src/components/Cards/StatsCard.vue'
import LTable from 'src/components/Table.vue'

export default {
    components: {
        LTable,
        ChartCard,
        StatsCard
    },
    data() {
        return {
            metricsFetched: false,
            metrics: {
                'storage': {
                    "declared_space": null,
                    "used_space": null
                },
                "users": {
                    "active_accounts": null,
                    "active_jwt_keys": null
                },
                "backup": {
                    "versions": null,
                    "collections": null,
                    "recent_versions": []
                },
                "resources": {
                    "tags": 0
                }
            },
            backupsList: {
                data: []
            }
        }
    },

    methods: {
        fetchFromBackend() {
            let that = this

            this.$backend().fetchMetrics().then(function (metrics) {
                that.metrics = metrics
                that.backupsList.data = []
                that.metricsFetched = true

                that.$nextTick(function () {
                    for (let num in that.metrics.backup.recent_versions) {
                        let version = that.metrics.backup.recent_versions[num]
                        that.backupsList.data.push({
                            title: version.file.filename,
                            version: version.version,
                            creationDate: version.creationDate
                        })
                    }
                })
            })
        }
    },

    mounted() {
        this.fetchFromBackend()
    }
}
</script>

<style>
.card-body {
    padding-top: 0 !important;
    padding-bottom: 15px !important;
}
</style>
