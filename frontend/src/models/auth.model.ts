/**
 * Represents a single role/permission
 *
 * @api /api/stable/auth/roles
 */

// @ts-ignore
import {List} from "src/contracts/base.contract.ts";

export class Permission {
    id: string
    description: string
}

export class RolesList {
    permissions: List<Permission>
}
