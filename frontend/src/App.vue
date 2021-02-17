<template>
  <div :class="{'nav-open': $sidebar.showSidebar}">
    <loader :is-visible="isLoading"></loader>
    <notifications></notifications>
    <router-view></router-view>
  </div>
</template>

<script>
  import Loader from 'src/components/Loader.vue';
  import axios from 'axios';

  export default {
      components: {
          Loader
      },
      data: function () {
          return {
              isLoading: false,
              axiosInterceptor: null
          }
      },
      mounted() {
          this.enableInterceptor()
      },
      methods: {
          enableInterceptor() {
              this.axiosInterceptor = axios.interceptors.request.use((config) => {
                  this.isLoading = true
                  return config
              }, (error) => {
                  this.disableLoading()
                  return Promise.reject(error)
              })

              axios.interceptors.response.use((response) => {
                  this.disableLoading()
                  return response
              }, (error) => {
                  this.disableLoading()
                  return Promise.reject(error)
              })
          },

          disableLoading() {
              setTimeout(() => { this.isLoading = false }, 300)
          },

          disableInterceptor() {
              axios.interceptors.request.eject(this.axiosInterceptor)
          },
      },
  }
</script>
<style lang="scss">
  .vue-notifyjs.notifications{
    .list-move {
      transition: transform 0.3s, opacity 0.4s;
    }
    .list-item {
      display: inline-block;
      margin-right: 10px;

    }
    .list-enter-active {
      transition: transform 0.2s ease-in, opacity 0.4s ease-in;
    }
    .list-leave-active {
      transition: transform 1s ease-out, opacity 0.4s ease-out;
    }

    .list-enter {
      opacity: 0;
      transform: scale(1.1);

    }
    .list-leave-to {
      opacity: 0;
      transform: scale(1.2, 0.7);
    }
  }
</style>
