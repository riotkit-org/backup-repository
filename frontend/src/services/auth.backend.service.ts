// @ts-ignore
import BackupRepositoryBackend from './backend.service.ts';
// @ts-ignore
import {RolesList} from "src/models/auth.model.ts";
// @ts-ignore
import {BackupCollection} from "src/models/backup.model.ts";
// @ts-ignore
import {List} from "src/contracts/base.contract.ts";

export default class AuthBackend extends BackupRepositoryBackend {
    /**
     * Lists all roles
     *
     * @param limits Comma separated limits list eg. user,collection-specific
     *
     * @api /api/stable/auth/roles
     */
    async findRoles(limits: string = ''): Promise<RolesList> {
        return super.get('/auth/roles?limits=' + limits).then(function (response) {
            return RolesList.createFromRawDict(response.data.data)
        })
    }

    async updateRolesForCollection(collection: BackupCollection, userId: string, roles: List<string|any>): Promise<boolean> {
        let payload = {
            'user': userId,
            'roles': roles
        }

        return super.post('/repository/collection/' + collection.id + '/access', payload, "PUT").then(function(response) {
            window.console.info(response.data)

            return response.data.success === true
        })
    }
}
