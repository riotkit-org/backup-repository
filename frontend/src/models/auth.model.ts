/**
 * Represents a single role/permission
 *
 * @api /api/stable/auth/roles
 */

// @ts-ignore
import {List} from "src/contracts/base.contract.ts";
import Dictionary from "../contracts/base.contract";

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
