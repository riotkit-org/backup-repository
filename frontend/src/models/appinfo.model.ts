
export default class AppInfo {
    version: string
    dbType: string

    constructor(version: string, dbType: string) {
        this.version = version
        this.dbType = dbType
    }
}
