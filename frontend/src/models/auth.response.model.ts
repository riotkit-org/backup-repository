// @ts-ignore
import {RolesList, User} from "./auth.model.ts";
// @ts-ignore
import Pagination from "./pagination.model.ts";
// @ts-ignore
import {List} from "src/contracts/base.contract.ts";

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
