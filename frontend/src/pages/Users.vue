<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <card class="card-plain">
                        <template slot="header">
                            <h4 class="card-title">Users</h4>
                            <p class="card-category">Individual people or organization accounts can have assigned various levels of access basing on roles and attributes</p>
                        </template>
                        <div class="table-responsive">
                            <l-table class="table-hover"
                                     :columns="users.columns"
                                     :data="users.data">
                            </l-table>
                        </div>
                    </card>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="text-center">
                        <button type="submit" class="btn btn-info btn-fill float-right" @click.prevent="addNewUser">Add user</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LTable from 'src/components/Table.vue'
import Card from 'src/components/Cards/Card.vue'
const tableColumns = ['Id', 'Name', 'Email', 'Organization']

const tableData = [{
    id: 'a158d1a0-34f1-4581-830e-6c54fb19e765',
    name: 'marek-iwa',
    email: 'secretariado@iwa-ait.org',
    organization: 'International Workers Association',
    _url: '#/admin/user/22c1c0d5-002a-4100-8731-1bc297748410',
    _active: true
},
    {
        id: '22c1c0d5-002a-4100-8731-1bc297748410',
        name: 'anne-iwa',
        email: 'anne@iwa-ait.org',
        organization: 'International Workers Association',
        _url: '#/admin/user/22c1c0d5-002a-4100-8731-1bc297748410',
        _active: false
    }
]

export default {
    components: {
        LTable,
        Card
    },
    data () {
        return {
            users: {
                columns: [...tableColumns],
                data: [...tableData]
            }
        }
    },
    methods: {
        addNewUser() {
            window.location.href = '#/admin/user'
        }
    }
}
</script>
