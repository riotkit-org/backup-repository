// @ts-ignore
import BackupRepositoryBackend from './backend.service.ts';
// @ts-ignore
import {RolesList} from "../models/auth.model.ts";
// @ts-ignore
import {BackupCollection} from "../models/backup.model.ts";
// @ts-ignore
import {List} from "../contracts/base.contract.ts";
// @ts-ignore
import {User} from "../models/auth.model.ts";
// @ts-ignore
import {UserListingResponse} from "../models/auth.response.model.ts";
// @ts-ignore
import Pagination from "../models/pagination.model.ts";
// @ts-ignore
import {PermissionsResponse} from "../models/auth.response.model.ts";
// @ts-ignore
import {UserAccessResponse} from "../models/auth.response.model.ts";
// @ts-ignore
import {UserAccess} from "../models/auth.model.ts";

export default class AuthBackend extends BackupRepositoryBackend {
    /**
     * Lists permissions
     *
     * @param limits Comma separated limits list eg. auth,collection
     *
     * @api /api/stable/auth/permissions
     */
    async findPermissions(limits: string = ''): Promise<PermissionsResponse> {
        return super.get('/auth/permissions?limits=' + limits).then(function (response) {
            return new PermissionsResponse(
                RolesList.createFromRawDict(response.data.permissions),
                RolesList.createFromRawDict(response.data.allPermissions)
            )
        })
    }

    /**
     * Users search
     *
     * @param searchPhrase
     * @param page
     * @param limit
     */
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

    /**
     * Finds a single User profile
     *
     * @param userId
     */
    async findUserById(userId: string): Promise<User|null> {
        return super.get('/auth/user/' + userId).then(function (response) {
            if (response.data.status !== true) {
                return null
            }

            return User.fromDict(response.data.user)
        })
    }

    /**
     * Create or Edit User account
     *
     * @param user
     * @param isNewUser
     * @param currentPassword
     * @param newPassword
     * @param repeatNewPassword
     */
    async saveUser(user: User, isNewUser: boolean, currentPassword: string, newPassword: string, repeatNewPassword: string): Promise<any> {
        let url = isNewUser ? '/auth/user' : '/auth/user/' + user.id
        let method = isNewUser ? 'POST' : 'PUT'

        let data = user.toUserUpdatePayloadDict(newPassword, repeatNewPassword, currentPassword)

        return super.post(url, data, method).then(function (response) {
            return response.data.status === true
        })
    }

    /**
     * Deletes an user account
     *
     * @param userId
     */
    async deleteUserProfile(userId: string): Promise<boolean> {
        return super.delete('/auth/user/' + userId).then(function (response) {
            return response.data.status === true
        })
    }

    /**
     * Searches for Access Tokens
     */
    async findAccessTokens(page: number = 1): Promise<UserAccessResponse> {
        return super.get('/auth/token?page=' + page, true).then(function (response) {
            return new UserAccessResponse(
                response.data.data.map((accessToken) => UserAccess.fromDict(accessToken)),
                Pagination.fromDict(response.data.context.pagination)
            )
        })
    }

    /**
     * Revoke a session/api token
     *
     * @param tokenHash
     */
    async revokeToken(tokenHash: string): Promise<boolean> {
        return super.delete('/auth/token/' + tokenHash).then(function (response) {
            return response.data.status === true
        })
    }

    /**
     * Creates a token
     *
     * @param permissions
     * @param ttl
     * @param description
     */
    async createAccessToken(permissions: List<string>|never[], ttl: number, description: string): Promise<string> {
        return super.post('/auth/token', {'requestedPermissions': permissions, 'ttl': ttl, 'description': description}).then(function (response) {
            return response.data.token
        })
    }
}
