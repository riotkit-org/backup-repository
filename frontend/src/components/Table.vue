<template>
  <table class="table">
    <thead>
      <slot name="columns">
        <tr>
          <th v-for="column in columns" :key="column">{{column}}</th>
        </tr>
      </slot>
    </thead>
    <tbody>
        <tr v-for="(item, index) in data" :key="index" :class="item['_active'] ? 'active' : 'inactive'">
          <slot :row="item">
              <td v-for="column in columns" :key="column" v-if="hasValue(item, column)" :data-column="column">
                  <a :href="item['_url']" v-html="itemValue(item, column)" v-if="item['_url'] && typeof itemValue(item, column) === 'string' && !itemValue(item, column).includes('<button')"></a>
                  <span v-else v-html="itemValue(item, column)"></span>
              </td>
          </slot>
        </tr>
        <tr v-if="!data || !data.length">
            <td>
                No data to display
            </td>
            <td v-for="column in columns" :key="column"></td>
        </tr>
    </tbody>
  </table>
</template>
<script>
  export default {
    name: 'l-table',
    props: {
      columns: Array,
      data: Array
    },
    methods: {
      hasValue (item, column) {
        return item[column.toLowerCase()] !== 'undefined'
      },
      itemValue (item, column) {
        return item[column.toLowerCase()]
      }
    }
  }
</script>
<style>
.inactive a, .inactive a:hover {
    color: #9A9A9A;
}

.active a, .active a:hover {
    color: black;
}
</style>
