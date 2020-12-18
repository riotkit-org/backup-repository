<template>
    <div class="content">
        <div class="container-fluid">

            <!--
              -- Stats cards
              -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-warning">
                            <i class="nc-icon nc-chart text-warning"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Used disk space</p>
                            <h4 class="card-title">{{ stats.disk_used }}/{{ stats.disk_total }}</h4>
                        </div>
                    </stats-card>
                </div>

                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-success">
                            <i class="nc-icon nc-circle-09 text-success"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Users</p>
                            <h4 class="card-title">{{ stats.users_total }}</h4>
                        </div>
                    </stats-card>
                </div>

                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-danger">
                            <i class="nc-icon nc-vector text-danger"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Active versions</p>
                            <h4 class="card-title">{{ stats.total_active_versions }}</h4>
                        </div>
                    </stats-card>
                </div>

                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-info">
                            <i class="nc-icon nc-single-copy-04 text-primary"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Backup collections</p>
                            <h4 class="card-title">{{ stats.total_backup_collections }}</h4>
                        </div>
                    </stats-card>
                </div>
            </div>

            <!--
              -- Bigger panels with stats - charts and tables
              -->
            <div class="row">
                <div class="col-md-6">
                    <chart-card :chart-data="spaceUsageData.data" chart-type="Bar">
                        <template slot="header">
                            <h4 class="card-title">Allocation vs actual usage</h4>
                            <p class="card-category">Compares how many disk space is reserved and how many space is
                                actually used</p>
                        </template>
                        <template slot="footer">
                            <div class="legend">
                                <i class="fa fa-circle text-info"></i> Reserved
                                <i class="fa fa-circle text-danger"></i> Used
                            </div>
                        </template>
                    </chart-card>
                </div>

                <div class="col-md-6">
                    <card>
                        <template slot="header">
                            <h4 class="card-title">Recently submitted versions</h4>
                        </template>
                        <l-table :data="backupsList.data"
                                 :columns="backupsList.columns">
                            <template slot="columns"></template>

                            <template slot-scope="{row}">
                                <td>{{ row.title }} ({{ row.version }})</td>
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
import BackupRepositoryBackend from '../services/backend.service.ts'

export default {
    components: {
        LTable,
        ChartCard,
        StatsCard
    },
    data() {
        return {
            stats: {
                'disk_used': '105GB',
                'disk_total': '1024GB',
                'users_total': 15,
                'total_active_versions': 4954,
                'total_backup_collections': 400
            },
            spaceUsageData: {
                data: {
                    labels: ['Used', 'Reserved'],
                    series: [
                        [0, 542],
                        [412, 0]
                    ]
                },
                options: {
                    seriesBarDistance: 10,
                    axisX: {
                        showGrid: false
                    },
                    height: '245px'
                },
                responsiveOptions: [
                    ['screen and (max-width: 640px)', {
                        seriesBarDistance: 5,
                        axisX: {
                            labelInterpolationFnc(value) {
                                return value[0]
                            }
                        }
                    }]
                ]
            },
            backupsList: {
                data: [
                    {title: 'zsp.net.pl / database + files', version: "v532"},
                    {title: 'iwa-ait.org / database + files', version: "v874"},
                    {title: 'iwa-ait PostgreSQL (whole instance)', version: "v874"}
                ]
            }
        }
    }
}
</script>
<style>

</style>
