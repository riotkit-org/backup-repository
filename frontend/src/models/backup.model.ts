// @ts-ignore
import Dictionary from "src/contracts/base.contract.ts";

export default class BackupCollection {
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
}

export class CreationDate {
    date: string
    timezone_time: number
    timezone: string
}

export class BackupFile {
    id: number
    filename: string
}

export class BackupVersion {
    id: string
    version: string
    creationDate: CreationDate
    file: BackupFile
}
