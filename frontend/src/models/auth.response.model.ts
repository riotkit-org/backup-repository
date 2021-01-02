// @ts-ignore
import {RolesList, User, UserAccess} from "./auth.model.ts";
// @ts-ignore
import Pagination from "./pagination.model.ts";
// @ts-ignore
import {List} from "src/contracts/base.contract.ts";
// @ts-ignore
import {CreationDate} from "src/models/common.model.ts";
import Dictionary from "../contracts/base.contract";

export class UserListingResponse {
    users: List<User>|never[]
    pagination: Pagination

    constructor(users: List<User>|never[], pagination: Pagination) {
        this.users = users
        this.pagination = pagination
    }
}

export class PermissionsResponse {
    /**
     * Scoped means that list of permissions was limited by some scope eg. "only collection specific" or "only roles that user has"
     */
    scoped: RolesList

    /**
     * All roles available in the application
     */
    all: RolesList

    constructor(scoped: RolesList, all: RolesList) {
        this.scoped = scoped
        this.all = all
    }
}

export class UserAccessResponse {
    accessList: List<UserAccess>|never[]
    pagination: Pagination

    constructor(accessList: List<UserAccess>|never[], pagination: Pagination) {
        this.accessList = accessList
        this.pagination = pagination
    }
}
