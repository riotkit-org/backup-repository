<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6">
                    <card class="card-plain">
                        <template slot="header">
                            <h4 class="card-title">Stored versions</h4>
                            <p class="card-category">
                                Recently stored versions in this collection
                            </p>
                        </template>
                        <div class="table-responsive">
                            <l-table class="table-hover"
                                     :columns="collections.columns"
                                     :data="collections.data">
                            </l-table>
                        </div>
                    </card>
                </div>

                <div class="col-6">
                    <card>
                        <h4 slot="header" class="card-title">Edit Backup Collection</h4>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <base-input type="text"
                                                label="Organization"
                                                placeholder="Mining Workers Cooperative"
                                                >
                                    </base-input>
                                </div>
                                <div class="col-md-6">
                                    <base-input type="email"
                                                label="Email"
                                                placeholder="someaddress@iwa-ait.org"
                                                >
                                    </base-input>
                                </div>
                            </div>
                        </form>
                    </card>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LTable from 'src/components/Table.vue'
import Card from 'src/components/Cards/Card.vue'
import vPagination from 'vue-plain-pagination'
import BackupVersion from 'src/models/backup.model.ts'

let exampleModel = new BackupVersion()
exampleModel.id = 'a158d1a0-34f1-4581-830e-6c54fb19e765'
exampleModel.date = '2020-05-01 08:00:00'
exampleModel.version = 'v1'

const tableColumns = ['Version', 'Id', 'Date']

const tableData = [{
    version: exampleModel.version,
    id: exampleModel.id,
    date: exampleModel.date
}]

export default {
    components: {
        LTable,
        Card,
        vPagination
    },
    data () {
        return {
            collections: {
                columns: [...tableColumns],
                data: [...tableData]
            },
            currentPage: 1,
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
        addNewBackupCollection() {
            window.location.href = '#/admin/backup/collection'
        },

        fetchBackendData() {
            
        }
    }
}
</script>
