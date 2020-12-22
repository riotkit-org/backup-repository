<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6" v-if="isEditing && collection">
                    <versions-list :collection="collection"></versions-list>
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
                        <button type="submit" class="btn btn-info btn-fill float-right" @click.prevent="saveChanges" v-html="isEditing ? 'Save changes' : 'Create'"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Card from 'src/components/Cards/Card.vue'
import VersionsList from 'src/components/Collections/VersionsList.vue'

export default {
    components: {
        Card, VersionsList
    },
    data () {
        return {
            isEditing: false,
            collection: null
        }
    },
    methods: {
        saveChanges() {
            let that = this

            this.$backend().saveBackupCollection(this.collection).then(function (status) {
                if (status === true) {
                    that.$notifications.notify({
                        message: 'Changes saved',
                        horizontalAlign: 'right',
                        verticalAlign: 'top',
                        type: 'success'
                    })
                }
            })
        },

        fetchBackendData() {
            let collectionId = this.$route.params.pathMatch
            let that = this

            this.$backend().findBackupCollectionById(collectionId).then(function (collection) {
                that.collection = collection
            })
        }
    },
    mounted() {
        let collectionId = this.$route.params.pathMatch
        this.isEditing = collectionId !== undefined

        if (collectionId) {
            this.fetchBackendData()
        }
    }
}
</script>
