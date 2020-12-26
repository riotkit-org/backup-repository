/**
 * Represents a single role/permission
 *
 * @api /api/stable/auth/roles
 */

// @ts-ignore
import {List} from "src/contracts/base.contract.ts";
import Dictionary from "../contracts/base.contract";

export class User {
    id: string
    email: string
    active: boolean
    data: Dictionary<any>
    roles: List<string>
    expires: string
    expired: boolean

    static fromDict(userData: Dictionary<any>) {
        let user = new User()
        user.id      = userData['id']
        user.email   = userData['email']
        user.expired = userData['expired']
        user.expires = userData['expires']
        user.active  = userData['active']
        user.roles   = userData['roles']

        return user
    }
}

/**
 * active: true
 data: {tags: ["international-workers-association", "iwa-ait.org"], maxAllowedFileSize: 1073741824,…}
 allowedIpAddresses: []
 allowedUserAgents: ["Mozilla XYZ", "curl/15.55 (RiotKit)"]
 maxAllowedFileSize: 1073741824
 tags: ["international-workers-association", "iwa-ait.org"]
 email: "example3@riseup.net"
 expired: false
 expires: "2021-05-01 01:06:01"
 id: "ca6a2635-d2cb-4682-ba81-3879dd0e8372"
 idIsCensored: false
 roles: ["collections.create_new", "collections.manage_users_in_allowed_collections",…]
 */

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
}
