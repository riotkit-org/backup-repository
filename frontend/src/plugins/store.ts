import Vuex from 'vuex'
import Vue from 'vue'
import createPersistedState from 'vuex-persistedstate'

Vue.use(Vuex)

const store = new Vuex.Store({
    plugins: [createPersistedState({
        storage: window.sessionStorage,
    })],
    state: {
        // @ts-ignore
        userJwt: '',
        isAuthenticated: false
    },
    mutations: {
        setJwt(state, jwt: string) {
            state.userJwt = jwt
            state.isAuthenticated = true
        }
    }
})

export default store
