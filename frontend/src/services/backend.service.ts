
import axios from 'axios';
import VueComponent from 'vue'
import Dictionary from '../contracts/base.contract';
// @ts-ignore
import AppInfo from '../models/appinfo.model.ts'

export default class BackupRepositoryBackend {
    vue: any|VueComponent

    constructor(component: VueComponent) {
        this.vue = component
    }

    getBaseURL() {
        return 'http://localhost:8000/api/stable'
    }

    /**
     * Loads application version information
     */
    async getApplicationInfo(): Promise<AppInfo|null> {
        return await this._get('/version', false).then(function (response) {
            if (response.data.version) {
                return new AppInfo(response.data.version, response.data.dbType)
            }

            return null
        })
    }

    /**
     * Returns a JWT token on successful login
     *
     * @param login
     * @param password
     */
    async getJWT(login: string, password: string): Promise<string|undefined> {
        return this._post('/login_check', {
            'username': login,
            'password': password
        }).then(function (response) {
            return response.data.token
        })
    }

    async _post(url: string, data: Dictionary<string>): Promise<any> {
        this.prepareAuthentication()

        let that = this
        let response

        await axios.post(this.getBaseURL() + url, data).then(function (onResponse) {
            response = onResponse

        }).catch(function (onError) {
            response = onError.response
            that._notify(response.data.message ? response.data.message : response.statusText)
        })

        return response
    }

    async _get(url: string, notifyErrors: boolean = true): Promise<any> {
        this.prepareAuthentication()

        let that = this
        let response

        await axios.get(this.getBaseURL() + url).then(function (onResponse) {
            response = onResponse

        }).catch(function (onError) {
            response = onError.response

            if (notifyErrors && response) {
                that._notify(response.data && response.data.message ? response.data.message : response.statusText)
            }

            if (!response) {
                window.console.warn('Invalid response:', onError)
            }
        })

        return response
    }

    prepareAuthentication() {
        if (this.vue.$auth().isLoggedIn()) {
            axios.defaults.headers.common = {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + this.vue.$auth().getJWT()
            }
        }
    }

    _notify(msg: string) {
        this.vue.$notifications.notify({
            message: msg,
            horizontalAlign: 'right',
            verticalAlign: 'top',
            type: 'danger'
        })
    }
}
