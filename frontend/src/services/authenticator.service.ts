
import {Store} from 'vuex'

export default class AuthenticatorService {
    store: typeof Store|any

    constructor(store: typeof Store) {
        this.store = store
    }

    setAuthentication(token: string) {
        this.store.commit('setJwt', token)
    }

    logout() {
        this.setAuthentication('')
    }

    isLoggedIn(): boolean {
        return this.store.state.userJwt != '' && this.store.state.userJwt != undefined
    }

    getJWT(): string {
        return this.store.state.userJwt
    }
}
