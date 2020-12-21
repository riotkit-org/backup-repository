<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <card class="card-plain">
                        <template slot="header">
                            <h4 class="card-title">Backup Collections</h4>
                            <p class="card-category">
                                Shows all backup collections current user has access to. Backup collection is a slot where versions of given subject could be stored.
                            </p>
                        </template>

                        <template slot="filters">
                            <div id="filter-panel" class="filter-panel">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <form class="form-inline" role="form">
                                            <div class="form-group">
                                                <label class="filter-col">Search:</label>
                                                <input type="text" v-debounce:500ms="fetchBackendData" v-model="filters.searchQuery" class="form-control input-sm">
                                            </div>
                                            <!-- @todo: Implement tags support - https://github.com/riotkit-org/file-repository/issues/121 -->
                                            <div class="form-group" style="display: none;">
                                                <label class="filter-col">Tags:</label>
                                                <vue-tags-input
                                                    v-model="filters._tags"
                                                    placeholder="Add tag=value"
                                                    @tags-changed="tagsUpdated"
                                                />
                                            </div>
                                            <div class="form-group">
                                                <label class="filter-col">Creation date:</label>
                                                <date-picker :range="filters.range" @range-changed="dateRangeUpdated"/>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="table-responsive">
                            <l-table class="table-hover"
                                     :columns="collections.columns"
                                     :data="collections.data">
                            </l-table>
                        </div>
                    </card>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <v-pagination v-model="currentPage" :page-count="maxPages" :classes="bootstrapPaginationClasses"></v-pagination>
                </div>

                <div class="col-6">
                    <div class="text-center">
                        <button type="submit" class="btn btn-info btn-fill float-right" @click.prevent="addNewBackupCollection">Create a new backup collection</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LTable from 'src/components/Table.vue'
import Card from 'src/components/Cards/Card.vue'
import DatePicker from 'src/components/Filters/DatePicker.vue'
import vPagination from 'vue-plain-pagination'
import VueTagsInput from '@johmun/vue-tags-input';
import byteSize from 'byte-size'

export default {
    components: {
        LTable,
        Card,
        vPagination,
        VueTagsInput,
        DatePicker
    },
    data () {
        return {
            collections: {
                columns: ['Id', 'Filename', 'Limits', 'Description'],
                data: []
            },
            filters: {
                searchQuery: '',
                tags: [],
                _tags: '',
                range: {
                    start: null,
                    end: null
                }
            },
            searchQuery: '',
            currentPage: 1,
            maxPages: 1,
            bootstrapPaginationClasses: {
                ul: 'pagination',
                li: 'page-item',
                liActive: 'active',
                liDisable: 'disabled',
                button: 'page-link'
            }
        }
    },
    created() {
        this.fetchBackendData()
    },
    methods: {
        addNewBackupCollection() {
            window.location.href = '#/admin/backup/collection'
        },

        fetchBackendData() {
            let that = this

            this.$backend().getBackupCollections(this.currentPage, this.filters.searchQuery, this.filters.range.start, this.filters.range.end, this.filters.tags)
                .then(function (response) {
                    // pagination
                    that.maxPages    = response.pagination.max

                    // filling up the table
                    that.collections.data = []

                    for (let num in response.elements) {
                        /**
                         * @type {BackupCollection}
                         */
                        let collection = response.elements[num]

                        that.collections.data.push({
                            id: '<small>' + collection.id + '</small>',
                            filename: collection.filename,
                            limits: collection.maxBackupsCount + 'x' + byteSize(collection.maxOneBackupVersionSize) + ' (' + byteSize(collection.maxCollectionSize) + ')',
                            description: '<i>' + collection.description + '</i>',
                            _url: '#/admin/backup/collection/' + collection.id,
                            _active: true
                        })
                    }
                })
        },

        /**
         * When the date picker is updated
         */
        dateRangeUpdated(value) {
            this.filters.range = value
            this.fetchBackendData()
        },

        tagsUpdated(value) {
            this.filters.tags = value
            this.fetchBackendData()
        }
    },
    watch: {
        'currentPage': function () {
            this.fetchBackendData()
        }
    }
}
</script>

<style>
.date-panel-arrow {
    width: 25px;
}

.card .vc-grid-cell {
    padding: 0 !important;
}
</style>
