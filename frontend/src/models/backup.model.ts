// @ts-ignore
import Dictionary from "src/contracts/base.contract.ts";
// @ts-ignore
import {RolesList} from "./auth.model.ts";
// @ts-ignore
import {PhpDate} from "./common.model.ts"

import bytes from 'bytes'

export class BackupCollection {
    id: string = ''
    maxBackupsCount: number = 2
    maxOneBackupVersionSize: number = 52428800  // default 50 MB
    maxCollectionSize: number = 115343360       // default 110 MB
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
            maxOneVersionSize: this.getPrettyMaxOneVersionSize(),
            maxCollectionSize: this.getPrettyMaxCollectionSize(),
            strategy: this.strategy,
            description: this.description,
            filename: this.filename
        }
    }

    // @todo: Fix issue with inconsistent behavior on read/write bytes related to parsing bytes

    getPrettyMaxOneVersionSize(): string {
        return bytes(this.maxOneBackupVersionSize)
    }

    setMaxOneVersionSize(value: string): void {
        this.maxOneBackupVersionSize = bytes.parse(value)
    }

    getPrettyMaxCollectionSize(): string {
        return bytes(this.maxCollectionSize)
    }

    setMaxCollectionSize(value: string): void {
        this.maxCollectionSize = bytes.parse(value)
    }
}

export class BackupFile {
    id: number
    filename: string

    constructor(id: number, filename: string) {
        this.id = id
        this.filename = filename
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
            data['details']['creationDate']['date'],
            data['details']['creationDate']['timezone_type'],
            data['details']['creationDate']['timezone']
        )
        version.file = new BackupFile(data['details']['file']['id'], data['details']['file']['filename'])

        return version
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
            accessData['userId'],
            accessData['userEmail'],
            accessData['permissions']
        )
    }
}
