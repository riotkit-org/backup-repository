
import axios from 'axios';
import VueComponent from 'vue'

// @ts-ignore
import Dictionary from 'src/contracts/base.contract.ts';
// @ts-ignore
import AppInfo from 'src/models/appinfo.model.ts'
// @ts-ignore
import {BackupVersion} from "../models/backup.model.ts";

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
        return await this.get('/version', false).then(function (response) {
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
        return this.post('/login_check', {
            'username': login,
            'password': password
        }).then(function (response) {
            return response.data.token
        })
    }

    /**
     * Fetches metrics for dashboard
     */
    async fetchMetrics(): Promise<string|any> {
        return this.get('/metrics').then(function (response) {
            let metrics = response.data.data
            metrics.backup.recent_versions = metrics.backup.recent_versions.map(function (versionDict) {
                return BackupVersion.fromDict({details: versionDict})
            })

            return metrics
        })
    }

    async post(url: string, data: Dictionary<string|any>, method: string|any = "POST"): Promise<any> {
        this.prepareAuthentication()

        let that = this
        let response

        await axios.request({url: this.getBaseURL() + url, method: method, data: data}).then(function (onResponse) {
            response = onResponse

        }).catch(function (onError) {
            response = onError.response
            that.onResponseError(onError, true)
        })

        return response
    }

    async get(url: string, notifyErrors: boolean = true, method: string|any = "GET"): Promise<any> {
        this.prepareAuthentication()

        let that = this
        let response

        await axios.request({url: this.getBaseURL() + url, method: method}).then(function (onResponse) {
            response = onResponse

        }).catch(function (onError) {
            response = onError.response
            that.onResponseError(onError, notifyErrors)
        })

        return response
    }

    async delete(url: string, notifyErrors: boolean = true): Promise<any> {
        return this.get(url, notifyErrors, "DELETE")
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

    onResponseError(onError: any, notifyErrors: boolean) {
        // validation.error
        let response = onError.response

        if (notifyErrors && response) {
            let msgAttribute = [response.data.message, response.data.error, response.statusText]

            for (let errMsg in msgAttribute) {
                if (msgAttribute[errMsg]) {
                    this._notify(msgAttribute[errMsg])
                    break
                }
            }
        }

        if (response.data.fields) {
            for (let fieldName in response.data.fields) {
                let fieldDetails = response.data.fields[fieldName]
                this._notify(fieldName + ': ' + fieldDetails['message'])
            }
        }

        if (!response) {
            window.console.warn('Invalid response:', onError)
        }
    }
}
