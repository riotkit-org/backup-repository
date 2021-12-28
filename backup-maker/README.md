Backup Maker
============

Tiny backup client packed in a single binary.

**Features:**
- Captures output from user-defined Backup/Restore commands
- Automated GPG support enables easy E2E encryption
- Buffered upload of backup made on-the-fly requires no additional disk space to create backup
- Small, single binary, can be injected into container or distributed as a lightweight container

Usage
-----

### Creating backup example

```bash
export BM_AUTH_TOKEN="some-token"; \
export BM_COLLECTION_ID="111-222-333-444"; \
export BM_PASSPHRASE="riotkit"; \
${BIN_PATH} make --url https://example.org \
    -c "tar -zcvf - ./" \
    --key build/test/backup.key \
    --recipient test@riotkit.org \
    --log-level info
```

Environment variables
---------------------

Environment variables are optional, if present will cover values of appropriate commandline switches.

| Type    | Name                | Description                                                                               |
|---------|---------------------|-------------------------------------------------------------------------------------------|
| path    | BM_PUBLIC_KEY_PATH  | Path to the public key used for encryption                                                |
| string  | BM_CMD              | Command used to encrypt or decrypt (depends on context)                                   |
| string  | BM_PASSPHRASE       | Passphrase for the GPG key                                                                |
| string  | BM_VERSION          | Version to restore (defaults to "latest"), e.g. v1                                        |
| email   | BM_RECIPIENT        | E-mail address of GPG recipient key                                                       |
| url     | BM_URL              | Backup Repository URL address e.g. https://example.org                                    |
| uuidv4  | BM_COLLECTION_ID    | Existing collection ID                                                                    |
| jwt     | BM_AUTH_TOKEN       | JSON Web Token generated in Backup Repository that allows to write to given collection id |
| integer | BM_TIMEOUT          | Connection and read timeouts in seconds                                                   |
| path    | BM_PRIVATE_KEY_PATH | GPG private key used to decrypt backup                                                    |
