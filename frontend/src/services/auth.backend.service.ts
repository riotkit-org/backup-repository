// @ts-ignore
import BackupRepositoryBackend from './backend.service.ts';
// @ts-ignore
import {RolesList} from "src/models/auth.model.ts";
// @ts-ignore
import {BackupCollection} from "src/models/backup.model.ts";
// @ts-ignore
import {List} from "src/contracts/base.contract.ts";
// @ts-ignore
import {User} from "src/models/auth.model.ts";

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

    async findUsers(): Promise<List<User>|never[]> {
        return super.get('/auth/user/search?limit=1000&page=1').then(function (response) {
            if (!response.data.data) {
                return []
            }

            return response.data.data.map(function (userData) {
                return User.fromDict(userData)
            })
        })
    }
}
