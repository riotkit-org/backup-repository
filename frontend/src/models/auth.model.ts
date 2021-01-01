/**
 * Represents a single role/permission
 *
 * @api /api/stable/auth/roles
 */

// @ts-ignore
import {List} from "src/contracts/base.contract.ts";
// @ts-ignore
import Dictionary from "src/contracts/base.contract.ts";

/**
 * Details - mostly restrictions eg. allowed IP addresses
 */
export class UserDetails {
    tags: List<string>|never[] = []
    maxAllowedFileSize: number = 0
    allowedIpAddresses: List<string>|never[] = []
    allowedUserAgents: List<string>|never[]  = []

    static fromDict(data: Dictionary<any>) {
        let details = new UserDetails()
        details.tags = data['tags']
        details.maxAllowedFileSize = data['maxAllowedFileSize']
        details.allowedIpAddresses = data['allowedIpAddresses']
        details.allowedUserAgents  = data['allowedUserAgents']

        return details
    }

    toUserUpdatePayloadDict() {
        return {
            'tags': this.tags,
            'maxAllowedFileSize': this.maxAllowedFileSize,
            'allowedIpAddresses': this.allowedIpAddresses,
            'allowedUserAgents': this.allowedUserAgents
        }
    }
}

/**
 * User account
 */
export class User {
    id: string = ''
    email: string = ''
    active: boolean = true
    data: UserDetails
    roles: List<string>|never[] = []
    expires: string = ''
    expired: boolean = false
    about: string = ''
    organization: string = ''
    isAdmin: boolean = false

    constructor() {
        this.data = new UserDetails()
    }

    static fromDict(userData: Dictionary<any>) {
        let user = new User()
        user.id           = userData['id']
        user.email        = userData['email']
        user.expired      = userData['expired']
        user.expires      = userData['expires']
        user.active       = userData['active']
        user.roles        = userData['roles']
        user.about        = userData['about']
        user.organization = userData['organization']
        user.isAdmin      = userData['is_administrator']
        user.data         = UserDetails.fromDict(userData['data'])

        return user
    }

    /**
     * Create a payload required to Create/Update User account
     *
     * @param newPassword
     * @param repeatNewPassword
     * @param currentPassword
     */
    toUserUpdatePayloadDict(newPassword: string, repeatNewPassword: string, currentPassword: string) {
        let data = {
            'password': newPassword,
            'repeatPassword': repeatNewPassword,
            'currentPassword': currentPassword,

            // fields in edit and in creation
            'expires': this.expires,
            'active': this.active,
            'roles': this.roles,
            'about': this.about,
            'organization': this.organization,
            'data': this.data.toUserUpdatePayloadDict()
        }

        if (!this.id) {
            data['email'] = this.email
            data['expired'] = this.expired
        }

        return data
    }
}

export class Permission {
    id: string
    description: string

    constructor(id: string, description: string) {
        this.id = id
        this.description = description
    }
}

export class RolesList {
    permissions: List<Permission|any> = []

    static createFromRawDict(rawDict: Dictionary<string>): RolesList {
        let perms = new RolesList()

        for (let roleName in rawDict) {
            perms.permissions.push(new Permission(roleName, rawDict[roleName]))
        }

        return perms
    }

    toList(): List<string> {
        let roles: List<string|any> = []

        for (let num in this.permissions) {
            roles.push(this.permissions[num].id)
        }

        return roles
    }

    includes(permissionId) {
        // @ts-ignore
        return this.toList().includes(permissionId)
    }
}
