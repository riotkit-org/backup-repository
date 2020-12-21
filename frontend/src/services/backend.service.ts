
import axios from 'axios';
import VueComponent from 'vue'

// @ts-ignore
import Dictionary from 'src/contracts/base.contract.ts';
// @ts-ignore
import AppInfo from 'src/models/appinfo.model.ts'
// @ts-ignore
import BackupCollection from "src/models/backup.model.ts";
// @ts-ignore
import BackupCollectionsResponse from "src/models/backup.response.model.ts";
// @ts-ignore
import Pagination from "src/models/pagination.model.ts";
import {List} from "../contracts/base.contract";

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

    /**
     * Fetches backup collection and returns as a paginated respnose
     *
     * @param pageNum
     * @param qs
     * @param startDate
     * @param endDate
     * @param tags
     */
    async getBackupCollections(pageNum: number = 1, qs: string = '', startDate: Date|null, endDate: Date|null, tags: List<any>): Promise<BackupCollectionsResponse> {
        let url = '/repository/collection?page=' + pageNum + '&searchQuery=' + qs

        if (startDate !== null && endDate !== null) {
            url += '&createdFrom=' + startDate.toLocaleDateString("en-US") + '&createdTo=' + endDate.toLocaleDateString("en-US")
        }

        if (tags && tags.length > 0) {
            for (let tagNum in tags) {
                let tagData = tags[tagNum].text.split('=')
                let tagName = tagData[0]
                let tagValue = tagData[1] == undefined ? '' : tagData[1]

                url += '&tags[' + tagName + ']=' + tagValue
            }
        }

        return this._get(url)
            .then(function (response) {
                return new BackupCollectionsResponse(
                    Pagination.fromDict(response.data.pagination),
                    response.data.elements ? response.data.elements.map(function (elementData: Dictionary<any>) {
                        return BackupCollection.fromDict(elementData)
                    }) : []
                )
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
