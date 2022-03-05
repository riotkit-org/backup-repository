Administrative API endpoints
============================

## GET `/ready`

**Parameters:**
- `?code` or header `Authorization: ...` to pass secret passphrase defined in server's startup commandline switch `--health-check-key`

**Example:**

```bash
curl -s -X GET 'http://localhost:8080/ready'
```

**Example response (200):**

```json
{
    "data": {
        "health": [
            {
                "message": "OK",
                "name": "DbValidator",
                "status": true,
                "statusText": "DbValidator=true"
            },
            {
                "message": "OK",
                "name": "StorageAvailabilityValidator",
                "status": true,
                "statusText": "StorageAvailabilityValidator=true"
            },
            {
                "message": "OK",
                "name": "ConfigurationProviderValidator",
                "status": true,
                "statusText": "ConfigurationProviderValidator=true"
            }
        ]
    },
    "status": true
}
```

**Unauthorized response (403):**

```json
{
    "data": {},
    "error": "health code invalid. Should be provided withing 'Authorization' header or 'code' query string. Must match --health-check-code commandline switch value",
    "status": false
}
```

**Example error response (500):**

```json
{
    "data": {
        "health": [
            {
                "message": "OK",
                "name": "DbValidator",
                "status": true,
                "statusText": "DbValidator=true"
            },
            {
                "message": "storage not operable: blob (key \".health-1646488208126295105\") (code=Unknown): RequestError: send request failed\ncaused by: Put \"http://minio.backup-repository.svc.cluster.local:9000/backups/.health-1646488208126295105\": dial tcp 10.43.153.2:9000: i/o timeout",
                "name": "StorageAvailabilityValidator",
                "status": false,
                "statusText": "StorageAvailabilityValidator=false"
            },
            {
                "message": "OK",
                "name": "ConfigurationProviderValidator",
                "status": true,
                "statusText": "ConfigurationProviderValidator=true"
            }
        ]
    },
    "error": "one of checks failed",
    "status": false
}
```

## GET `/health`

**Example response (200):**

```json
{
    "data": {
        "msg": "The server is up and running. Dependent services are not shown there. Take a look at /ready endpoint"
    },
    "status": true
}
```

**500:**

There is no error response, if the server is unhealthy then it will not respond, there will be a gateway timeout on reverse proxy or connection timeout on client side.
