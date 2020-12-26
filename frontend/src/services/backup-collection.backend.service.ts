// @ts-ignore
import BackupRepositoryBackend from './backend.service.ts';
// @ts-ignore
import { BackupVersion } from "src/models/backup.model.ts";
// @ts-ignore
import { BackupCollection } from "src/models/backup.model.ts";
// @ts-ignore
import BackupCollectionsResponse from "src/models/backup.response.model.ts";
// @ts-ignore
import Pagination from "src/models/pagination.model.ts";
// @ts-ignore
import { List, Dictionary } from "src/contracts/base.contract.ts";
// @ts-ignore
import {AuthorizedAccess} from "src/models/backup.model.ts";

export default class BackupCollectionBackendService extends BackupRepositoryBackend {
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

        return super.get(url)
            .then(function (response) {
                return new BackupCollectionsResponse(
                    Pagination.fromDict(response.data.pagination),
                    response.data.elements ? response.data.elements.map(function (elementData: Dictionary<any>) {
                        return BackupCollection.fromDict(elementData)
                    }) : []
                )
            })
    }

    async saveBackupCollection(collection: BackupCollection): Promise<false|string> {
        let method = collection.id ? 'PUT' : 'POST' // are we editing or creating?

        return super.post('/repository/collection', collection.toDict(), method).then(function (response) {
            if (!response.data.status) {
                return false
            }

            return response.data.collection.id
        })
    }

    async findBackupCollectionById(collectionId: string): Promise<BackupCollection|null> {
        return super.get('/repository/collection/' + collectionId).then(function (response) {
            if (response.data.collection === undefined) {
                return null
            }

            return BackupCollection.fromDict(response.data.collection)
        })
    }

    async findVersionsForCollection(collection: BackupCollection): Promise<List<BackupVersion>> {
        return super.get('/repository/collection/' + collection.id + '/version').then(function (response) {

            // @ts-ignore
            let versions: List = []

            for (let versionNum in response.data.versions) {
                if (!response.data.versions.hasOwnProperty(versionNum)) {
                    continue
                }

                let version: Dictionary<string> = response.data.versions[versionNum]
                versions.push(BackupVersion.fromDict(version))
            }

            return versions
        })
    }

    async findAuthorizedUsersAndRoles(collection: BackupCollection): Promise<List<AuthorizedAccess>|never[]> {
        return super.get('/repository/collection/' + collection.id + '/access').then(function (response) {
            if (!response.data.users) {
                return []
            }

            return response.data.users.map(function (accessData) {
                return AuthorizedAccess.fromDict(accessData)
            })
        })
    }

    /**
     * Give user an access to collection, or update that access
     *
     * @param collection
     * @param userId
     * @param roles
     * @param create
     */
    async createOrUpdateUserAccessForCollection(collection: BackupCollection, userId: string, roles: List<string|any>, create: boolean = false): Promise<boolean> {
        let payload = {
            'user': userId,
            'roles': roles
        }

        let method = create ? "POST" : "PUT"

        return super.post('/repository/collection/' + collection.id + '/access', payload, method).then(function(response) {
            return response.data.status === true
        })
    }

    /**
     * Remove user's access from a collection
     *
     * @param collection
     * @param userId
     */
    async revokeUserAccessInCollection(collection: BackupCollection, userId: string): Promise<boolean> {
        return super.delete('/repository/collection/' + collection.id + '/access/' + userId).then(function (response) {
            return response.data.status === true
        })
    }
}
