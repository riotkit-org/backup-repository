## Configuration

| Key | Environmental variable | Example value | Description |
|---------------------- |------------------------------------| -----------------------------------------------------------| --------------------------------------------------------------------------------------------------------------------------------------- |
| api.key | WFR_API_KEY                                      | MJVgHjqd6skwtAEhkLIDyhwA6LS2xuEmgr                         | Key required to put in every HTTP request to prove that the action is permitted |
| token.expiration.time | WFR_TEMP_TOKEN_TIME                | +1 hour                                                    | Application allows generating temporary tokens for given actions to users eg. to show user an upload form and to allow to upload a file |
| downloader.size_limit | WFR_DOWNLOADER_FILE_SIZE_LIMIT     | 1073741824                                                 | How much the external resource size can be. It's important when importing eg. an image from external server giving an URL address |
| downloader.mimes      | downloader.mimes                   | ["image/jpeg", "image/png"]                                | List of allowed mime types when importing from external HTTP server via URL address |
| storage.path          | WFR_STORAGE_PATH                   | /some/where/                                               | Path where to store the files. In simple usage of service (without external storage etc.) it's better to do not change this value. |
| storage.filesize      | WFR_STORAGE_MAX_FILE_SIZE          | 1073741824                                                 | How much the uploaded file size can be |
| storage.allowed_types | WFR_STORAGE_MIMES                  | ["image/jpeg", "image/png"]                                | Allowed file types for upload (could be overwritten when creating a user token) |
| db.options            | ---                                | {"driver": "pdo_sqlite", "path": "/some/where/db.sqlite3"} | Database connection options |
| weburl                | ---                                | https://cdn1.wolnosciowiec.net                             | Complete URL to the application, default: autodetect by hostname |

