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
// @ts-ignore
import {UserListingResponse} from "src/models/auth.response.model.ts";
// @ts-ignore
import Pagination from "src/models/pagination.model.ts";
// @ts-ignore
import {PermissionsResponse} from "src/models/auth.response.model.ts";

export default class AuthBackend extends BackupRepositoryBackend {
    /**
     * Lists permissions
     *
     * @param limits Comma separated limits list eg. auth,collection
     *
     * @api /api/stable/auth/roles
     */
    async findPermissions(limits: string = ''): Promise<PermissionsResponse> {
        return super.get('/auth/roles?limits=' + limits).then(function (response) {
            return new PermissionsResponse(
                RolesList.createFromRawDict(response.data.permissions),
                RolesList.createFromRawDict(response.data.allPermissions)
            )
        })
    }

    async findUsers(searchPhrase: string = '', page: number = 1, limit: number = 1000): Promise<UserListingResponse> {
        return super.get('/auth/user?limit=' + limit + '&page=' + page + '&q=' + searchPhrase).then(function (response) {
            if (!response.data.data) {
                return new UserListingResponse([], new Pagination(1, 1, 20))
            }

            return new UserListingResponse(
                response.data.data.map(function (userData) { return User.fromDict(userData) }),
                new Pagination(page, response.data.context.pagination.maxPages, limit)
            )
        })
    }

    async findUserById(userId: string): Promise<User|null> {
        return super.get('/auth/user/' + userId).then(function (response) {
            if (response.data.status !== true) {
                return null
            }

            return User.fromDict(response.data.user)
        })
    }

    async saveUser(user: User, isNewUser: boolean, currentPassword: string, newPassword: string, repeatNewPassword: string): Promise<any> {
        let url = isNewUser ? '/auth/user' : '/auth/user/' + user.id
        let method = isNewUser ? 'POST' : 'PUT'

        let data = user.toUserUpdatePayloadDict(newPassword, repeatNewPassword, currentPassword)

        return super.post(url, data, method).then(function (response) {
            return response.data.status === true
        })
    }
}
