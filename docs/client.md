Backup upload & download
========================

The server is accepting uploaded files using an HTTP endpoint in standardized format, [check out the API docs for upload endpoint before proceeding](./api/collections/README.md).

Basic usage with cURL
---------------------

1) **Receive authorization token to sign requests**

```bash
curl -s -X POST -d '{"username":"admin","password":"admin"}' \
    -H 'Content-Type: application/json' \
    'http://localhost/api/stable/auth/login' | jq '.data.token' -r
```

2) **Copy generated token (or export to variable in script)**

3) **Upload a file or a piped stream**

```bash
curl -vvv -X POST -H 'Authorization: Bearer {{put-token-here}}' -F "file=@./archive.tar.gz.asc" 'http://localhost/api/stable/repository/collection/iwa-ait/version'
```

4) **Download file (restoring a backup)**

```bash
curl -vvv -X GET -H 'Authorization: Bearer {{put-token-here}}' 'http://localhost/api/stable/repository/collection/iwa-ait/version/latest' > ./my-file.tar.gz.asc
```

### FAQ: How do I encrypt a file?

```bash
gpg --encrypt -r test@riotkit.org ./archive.tar.gz
```

### FAQ: How do I create GPG keys?

Setup a GPG keyring: https://linuxhint.com/gpg-command-ubuntu/

[Backup Maker](https://github.com/riotkit-org/br-backup-maker)
------------

This is an official client for Backup Repository, it **automates GPG operations** almost transparently to the user and performs all operations on buffers to be
lightweight.

### Backup

```bash
export BM_AUTH_TOKEN="some-token"             # JWT token
export BM_COLLECTION_ID="111-222-333-444"     # collection name/id

backup-maker make --url https://example.org \
    -c "tar -zcvf - ./" \           # backup command which output Backup Maker will send
    --key build/test/backup.pub \   # public key (or private) required to encrypt the file
    --recipient test@riotkit.org \  # target key recipient (usually is the same as key owner)
    --log-level info
```

### Restore

```bash
export BM_AUTH_TOKEN="some-token"
export BM_COLLECTION_ID="111-222-333-444"
export BM_PASSPHRASE="riotkit"

backup-maker restore --url https://example.org \
    -c "cat - > /tmp/test" \
    --private-key .build/test/backup.key \    # to decrypt we need a PRIVATE KEY
    --recipient test@riotkit.org \
    --log-level debug
```

### FAQ: How do I get public and private keys?

1. **Setup GPG keyring**

https://linuxhint.com/gpg-command-ubuntu/

2. **Export keys**

```bash
# public key
gpg --armor --export user@example.com > public_key.asc

# private key
gpg --list-secret-keys user@example.com  # list keys to find a id
gpg --export-secret-keys YOUR_ID_HERE > private.key
```
