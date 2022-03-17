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
curl -vvv -X POST -H 'Authorization: Bearer {{put-token-here}}' -F "file=@./archive.tar.gz.gpg" 'http://localhost/api/stable/repository/collection/iwa-ait/version'
```
