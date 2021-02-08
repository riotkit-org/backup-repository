<template>
    <card class="card-plain">
        <template slot="header">
            <h4 class="card-title">Stored versions</h4>
            <p class="card-category">
                Recently stored versions in this collection
            </p>
        </template>
        <div class="table-responsive versions-table">
            <l-table class="table-hover"
                     :columns="versions.columns"
                     :data="versions.data">
            </l-table>
        </div>
    </card>
</template>

<script>
import Card from 'src/components/Cards/Card.vue'
import LTable from 'src/components/Table.vue'

export default {
    components: {
        Card, LTable
    },
    props: {
        collection: {}
    },
    data() {
        return {
            versions: {
                columns: ['Version', 'Id', 'Date'],
                data: []
            }
        }
    },
    methods: {
        fetchBackend(collection) {
            if (!collection.id) {
                window.console.info('Not fetching the versions list')
                return false
            }

            window.console.info('Fetching versions list')

            let that = this

            this.$backupCollectionBackend().findVersionsForCollection(collection).then(function (versions) {
                that.versions.data = []

                for (let versionNum in versions) {
                    let version = versions[versionNum]

                    that.versions.data.push({
                        version: 'v' + version.version,
                        id: version.id,
                        date: version.creationDate.date,
                        _url: '',
                        _active: true
                    })
                }
            })
        }
    },
    mounted() {
        this.fetchBackend(this.collection)
    },
    watch: {
        'collection': function () {
            this.fetchBackend(this.collection)
        }
    }
}
</script>
