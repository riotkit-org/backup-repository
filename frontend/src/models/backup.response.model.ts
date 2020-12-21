import Pagination from "./pagination.model";
import BackupCollection from "./backup.model";
import {List} from "../contracts/base.contract";

export default class BackupCollectionsResponse {
    pagination: Pagination
    elements: List<BackupCollection>;

    constructor(pagination: Pagination, elements: List<BackupCollection>) {
        this.pagination = pagination
        this.elements = elements
    }
}
