// @ts-ignore
import Dictionary from "src/contracts/base.contract.ts";
import bytes from 'bytes'

export class BackupCollection {
    id: string
    maxBackupsCount: number
    maxOneBackupVersionSize: number
    maxCollectionSize: number
    createdAt: string
    strategy: string
    description: string
    filename: string

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

export class CreationDate {
    date: string
    timezone_time: number
    timezone: string

    constructor(date: string, timezone_time: number, timezone: string) {
        this.date = date
        this.timezone_time = timezone_time
        this.timezone = timezone
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
    creationDate: CreationDate
    file: BackupFile

    static fromDict(data: Dictionary<string>|any): BackupVersion {
        let version = new BackupVersion()
        version.id = data['details']['id']
        version.version = data['details']['version']

        version.creationDate = new CreationDate(
            data['details']['creationDate']['date'],
            data['details']['creationDate']['timezone_type'],
            data['details']['creationDate']['timezone']
        )
        version.file = new BackupFile(data['details']['file']['id'], data['details']['file']['filename'])

        return version
    }
}
