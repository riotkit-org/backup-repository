// @ts-ignore
import Dictionary from "src/contracts/base.contract.ts";
// @ts-ignore
import {RolesList} from "./auth.model.ts";
// @ts-ignore
import {PhpDate} from "./common.model.ts"

export class BackupCollection {
    id: string = ''
    maxBackupsCount: number = 2
    maxOneBackupVersionSize: string = ''  // default 50 MB
    maxCollectionSize: string = ''       // default 110 MB
    createdAt: string   = ''
    strategy: string    = 'delete_oldest_when_adding_new'
    description: string = ''
    filename: string    = ''

    /**
     * Factory method
     *
     * @param elementData
     */
    static fromDict(elementData: Dictionary<any>) {
        let collection: BackupCollection = new this()
        collection.id = elementData['id']
        collection.maxBackupsCount = elementData['max_backups_count']
        collection.maxCollectionSize = elementData['max_collection_size']
        collection.maxOneBackupVersionSize = elementData['max_one_backup_version_size']
        collection.createdAt = elementData['createdAt']
        collection.strategy = elementData['strategy']
        collection.description = elementData['description']
        collection.filename = elementData['filename']

        return collection
    }

    toDict(): Dictionary<string|any> {
        return {
            id: this.id,
            maxBackupsCount: typeof this.maxBackupsCount === 'string' ? parseInt(this.maxBackupsCount) : this.maxBackupsCount,
            maxOneVersionSize: this.getMaxOneVersionSize(),
            maxCollectionSize: this.getMaxCollectionSize(),
            strategy: this.strategy,
            description: this.description,
            filename: this.filename
        }
    }

    getMaxOneVersionSize(): string {
        return this.maxOneBackupVersionSize
    }

    setMaxOneVersionSize(value: string): void {
        this.maxOneBackupVersionSize = value
    }

    getMaxCollectionSize(): string {
        return this.maxCollectionSize
    }

    setMaxCollectionSize(value: string): void {
        this.maxCollectionSize = value
    }
}

export class BackupFile {
    id: number
    filename: string
    filesize: string

    constructor(id: number, filename: string, filesize: string) {
        this.id = id
        this.filename = filename
        this.filesize = filesize
    }
}

export class BackupVersion {
    id: string
    version: string
    creationDate: PhpDate
    file: BackupFile

    static fromDict(data: Dictionary<string>|any): BackupVersion {
        let version = new BackupVersion()
        version.id = data['details']['id']
        version.version = data['details']['version']

        version.creationDate = new PhpDate(
            data['details']['creation_date']['date'],
            data['details']['creation_date']['timezone_type'],
            data['details']['creation_date']['timezone']
        )
        version.file = new BackupFile(
            data['details']['file']['id'],
            data['details']['file']['filename'],
            data['details']['file']['filesize']
        )

        return version
    }

    getFilesize(): string {
        return this.file.filesize
    }
}

export class AuthorizedAccess {
    userId: string
    userEmail: string
    roles: Dictionary<string>

    constructor(userId: string, userEmail: string, roles: Dictionary<string>) {
        this.userId = userId
        this.userEmail = userEmail
        this.roles = roles
    }

    static fromDict(accessData: Dictionary<string|any>) {
        return new AuthorizedAccess(
            accessData['user_id'],
            accessData['user_email'],
            accessData['permissions']
        )
    }
}
