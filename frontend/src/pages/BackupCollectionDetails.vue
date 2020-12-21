<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6" v-if="collection">
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

                <div class="col-6" v-if="collection">
                    <card>
                        <h4 slot="header" class="card-title">Edit Backup Collection</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <base-input type="text"
                                            label="File name"
                                            placeholder="international-workers-association-db-and-files.tar.gz"
                                            :value="collection.filename"
                                            @input="(value) => collection.filename = value"
                                            >
                                </base-input>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group input-group">
                                    <label class="control-label">Strategy</label>
                                    <select v-model="collection.strategy" class="custom-select custom-select-md mb-3 form-control">
                                        <option value="delete_oldest_when_adding_new">FIFO - delete oldest on adding new</option>
                                        <option value="alert_when_backup_limit_reached">Alert on limit reached</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label class="control-label">Description</label>
                                <textarea class="form-control" v-model="collection.description" placeholder="Description"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <base-input type="number"
                                            label="Max backups count"
                                            placeholder="14"
                                            :value="collection.maxBackupsCount"
                                            @input="(value) => collection.maxBackupsCount = value"
                                />
                            </div>
                            <div class="col-md-4">
                                <base-input type="string"
                                            label="Max one version size"
                                            placeholder="150MB"
                                            :value="collection.getPrettyMaxOneVersionSize()"
                                            @input="(value) => collection.setMaxOneVersionSize(value)"
                                />
                            </div>
                            <div class="col-md-4">
                                <base-input type="string"
                                            label="Max overall collection size"
                                            placeholder="150MB"
                                            :value="collection.getPrettyMaxCollectionSize()"
                                            @input="(value) => collection.setMaxCollectionSize(value)"
                                />
                            </div>
                        </div>
                    </card>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="text-center">
                        <button type="submit" class="btn btn-info btn-fill float-right" @click.prevent="saveChanges">Save</button>
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
import {BackupVersion} from 'src/models/backup.model.ts'

let exampleModel = new BackupVersion()
exampleModel.id = 'a158d1a0-34f1-4581-830e-6c54fb19e765'
exampleModel.date = '2020-05-01 08:00:00'
exampleModel.version = 'v1'

const tableColumns = ['Version', 'Id', 'Date']

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
                data: []
            },
            currentPage: 1,
            bootstrapPaginationClasses: {
                ul: 'pagination',
                li: 'page-item',
                liActive: 'active',
                liDisable: 'disabled',
                button: 'page-link'
            },
            collection: null
        }
    },
    methods: {
        addNewBackupCollection() {
            window.location.href = '#/admin/backup/collection'
        },

        saveChanges() {

        },

        fetchBackendData() {
            let collectionId = this.$route.params.pathMatch
            let that = this

            this.$backend().findBackupCollectionById(collectionId).then(function (collection) {
                that.collection = collection

                that.$backend().findVersionsForCollection(collection).then(function (versions) {
                    that.collections.data = []

                    for (let versionNum in versions) {
                        let version = versions[versionNum]

                        that.collections.data.push({
                            version: version.version,
                            id: version.id,
                            date: version.creationDate.date,
                            _url: '',
                            _active: true
                        })
                    }
                })
            })
        }
    },
    mounted() {
        this.fetchBackendData()
    }
}
</script>
