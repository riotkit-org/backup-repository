<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-7" v-if="isEditing && collection">
                    <permissions-list :collection="collection"/>
                </div>

                <!--
                  -- Edit & Creation form
                  -->
                <div :class="isEditing ? 'col-5' : 'col-12'" v-if="collection">
                    <card>
                        <h4 slot="header" class="card-title" v-html="isEditing ? 'Edit Backup Collection' : 'Create Backup Collection'"></h4>
                        <div class="row">
                            <div class="col-md-6">
                                <base-input type="text"
                                            label="File name"
                                            data-field="Filename"
                                            placeholder="international-workers-association-db-and-files.tar.gz"
                                            :initial-value="collection.filename"
                                            @input="(value) => collection.filename = value"
                                            >
                                </base-input>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group input-group">
                                    <label class="control-label">Strategy</label>
                                    <select v-model="collection.strategy" class="custom-select custom-select-md mb-3 form-control" data-field="Strategy">
                                        <option value="delete_oldest_when_adding_new">FIFO - delete oldest on adding new</option>
                                        <option value="alert_when_backup_limit_reached">Alert on limit reached</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label class="control-label">Description</label>
                                <textarea class="form-control" v-model="collection.description" placeholder="Description" data-field="Description"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <base-input
                                    type="number"
                                    label="Max backups count"
                                    data-field="Max backups count"
                                    placeholder="14"
                                    :initial-value="collection.maxBackupsCount"
                                    @input="(value) => collection.maxBackupsCount = value"
                                />
                            </div>
                            <div class="col-md-4">
                                <base-input
                                    type="string"
                                    label="Max one version size"
                                    data-field="Max one version size"
                                    placeholder="150MB"
                                    :initial-value="collection.getPrettyMaxOneVersionSize()"
                                    @input="(value) => collection.setMaxOneVersionSize(value)"
                                />
                            </div>
                            <div class="col-md-4">
                                <base-input
                                    type="string"
                                    label="Max overall collection size"
                                    data-field="Max overall collection size"
                                    placeholder="150MB"
                                    :initial-value="collection.getPrettyMaxCollectionSize()"
                                    @input="(value) => collection.setMaxCollectionSize(value)"
                                />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="text-center" v-if="isEditing">
                                    <button type="submit" class="btn btn-info btn-fill float-left" @click.prevent="deleteCollection">Delete collection</button>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-info btn-fill float-right" @click.prevent="saveChanges" v-html="isEditing ? 'Save changes' : 'Create'"></button>
                                </div>
                            </div>
                        </div>
                    </card>
                </div>
            </div>

            <div class="row">
                <!--
                  -- List of backup versions
                  -->
                <div class="col-12" v-if="isEditing && collection">
                    <versions-list :collection="collection"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Card from 'src/components/Cards/Card.vue'
import VersionsList from 'src/components/Collections/VersionsList.vue'
import PermissionsList from 'src/components/Collections/PermissionsList.vue'
import {BackupCollection} from "src/models/backup.model.ts";

export default {
    components: {
        Card, VersionsList, PermissionsList
    },
    data () {
        return {
            isEditing: false,
            collection: new BackupCollection()
        }
    },
    methods: {
        saveChanges() {
            let that = this

            this.$backupCollectionBackend().saveBackupCollection(this.collection).then(function (collectionId) {
                if (collectionId !== false) {
                    that.$notifications.notify({
                        message: that.isEditing ? 'Changes saved' : 'Collection created',
                        horizontalAlign: 'right',
                        verticalAlign: 'top',
                        type: 'success'
                    })

                    // redirect if new collection was created
                    if (!that.isEditing) {
                        that.$router.push({name: 'backup_collections_list', query: {'searchQuery': collectionId}})
                    }
                }
            })
        },

        fetchBackendData() {
            let collectionId = this.$route.params.pathMatch
            let that = this

            this.$backupCollectionBackend().findBackupCollectionById(collectionId).then(function (collection) {
                that.collection = collection
            })
        },

        deleteCollection() {
            let that = this
            let confirmation = prompt('Are you sure you want to delete this collection? To confirm write "' + this.collection.filename + '"')

            if (confirmation === this.collection.filename) {
                this.$backupCollectionBackend().deleteCollection(this.collection).then(function (status) {
                    if (status === true) {
                        that.$notifications.notify({
                            message: 'Collection deleted',
                            horizontalAlign: 'right',
                            verticalAlign: 'top',
                            type: 'success'
                        })

                        that.$router.push({name: 'backup_collections_list'})
                    }
                })
            }
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
