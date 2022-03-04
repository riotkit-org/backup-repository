Administrative API endpoints
============================

## GET `/ready`

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

**Other responses:**
- [500](../common-responses.md)
