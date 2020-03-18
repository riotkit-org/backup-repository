.. _creating_a_token:

Creating a token
----------------

Check out the :ref:`permissions_reference` for a complete list of permissions.

================================  =======================================================================================================================================================
   Parameters
--------------------------------  -------------------------------------------------------------------------------------------------------------------------------------------------------
 name                               description
================================  =======================================================================================================================================================
roles                               A list of roles allowed for user. See permissions/configuration reference page
data.tags                           List of allowed tags to use in upload endpoints (OPTIONAL)
data.allowedMimeTypes               List of allowed mime types (OPTIONAL)
data.maxAllowedFileSize             Number of bytes of maximum file size (OPTIONAL)
data.allowedUserAgents              List of allowed User-Agent header values (ex. to restrict token to single browser) (OPTIONAL)
data.allowedIpAddresses             List of allowed IP addresses (ex. to restrict one-time-token to single person/session) (OPTIONAL)
data.secureCopyEncryptionMethod     Encryption method in SecureCopy mechanism [choices: aes-256-cbc] (if using) (OPTIONAL)
data.secureCopyEncryptionKey        Encryption key in SecureCopy component. If active, then client using this token will be downloading encrypted files (zero-knowledge) (OPTIONAL)
data.secureCopyDigestMethod         Digest algorithm [choices: sha512] (optional)
data.secureCopyDigestRounds         Digest rounds eg. 6000 (optional)
data.secureCopyDigestSalt           Digest salt used in OpenSSL to generate a hash
expires                             Expiration date, or "auto", "automatic", "never". Empty value means same as "auto"
id                                  Custom UUIDv4 (requires: *security.create_predictable_token_ids* role or to be an admin)
================================  =======================================================================================================================================================

.. code:: json

    POST /auth/token/generate?_token=your-admin-token-there

    {
        "roles": ["collections.create_new", "collections.manage_tokens_in_allowed_collections"],
        "data": {
            "tags": [],
            "allowedMimeTypes": ["image/jpeg", "image/png", "image/gif"],
            "maxAllowedFileSize": 14579,
            "allowedUserAgents": ["Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0"],
            "allowedIpAddresses": ["192.168.1.10"]
        },
        "expires": "2020-05-05 08:00:00"
    }

Example response:

.. code:: json

    {
        "status": true,
        "error_code": null,
        "http_code": 200,
        "errors": [],
        "context": [],
        "message": "Token created",
        "token": {
            "id": "ca6a2635-d2cb-4682-ba81-3879dd0e8a77",
            "active": true,
            "expires": "2020-05-05 08:00:00",
            "expired": false,
            "data": {
                "tags": [],
                "allowedMimeTypes": ["image/jpeg", "image/png", "image/gif"],
                "maxAllowedFileSize": 14579,
                "allowedIpAddresses": ["192.168.1.10"],
                "allowedUserAgents": ["Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0"],
                "secureCopyEncryptionKey": "",
                "secureCopyEncryptionMethod": ""
            },
            "roles": [
                "collections.create_new",
                "collections.add_tokens_to_allowed_collections"
            ]
        }
    }


Required roles:

    - security.generate_tokens

Searching tokens
----------------

Finds tokens matching search criteria.

.. code:: json

    GET /auth/search?_token=your-token-there&q=&limit=50&page=1


Example response:

.. code:: json

    {
        "status": true,
        "error_code": null,
        "http_code": 200,
        "errors": [],
        "context": {
            "pagination": {
                "page": 1,
                "perPageLimit": 5,
                "maxPages": 7
            }
        },
        "message": "Matches found",
        "data": [
            {
                "id": "1c2c84f2-d488-4ea0-9c88-d25aab139ac4",
                "active": true,
                "data": {
                    "tags": [],
                    "allowedMimeTypes": [],
                    "maxAllowedFileSize": null,
                    "allowedIpAddresses": [],
                    "allowedUserAgents": [],
                    "secureCopyEncryptionKey": "",
                    "secureCopyEncryptionMethod": ""
                },
                "roles": [
                    "upload.images"
                ]
            },
            {
                "id": "669d4918-b156-412d-9c89-ba01d6eef9d4",
                "active": true,
                "data": {
                    "tags": [],
                    "allowedMimeTypes": [],
                    "maxAllowedFileSize": null,
                    "allowedIpAddresses": [],
                    "allowedUserAgents": [],
                    "secureCopyEncryptionKey": "",
                    "secureCopyEncryptionMethod": ""
                },
                "roles": [
                    "security.generate_tokens"
                ]
            },
            {
                "id": "fad05629-51f6-4ddf-b21a-315a1451670d",
                "active": true,
                "data": {
                    "tags": [],
                    "allowedMimeTypes": [],
                    "maxAllowedFileSize": null,
                    "allowedIpAddresses": [],
                    "allowedUserAgents": [],
                    "secureCopyEncryptionKey": "",
                    "secureCopyEncryptionMethod": ""
                },
                "roles": [
                    "upload.images"
                ]
            },
            {
                "id": "3235ad82-666f-4963-a751-b4dff3168c4c",
                "active": true,
                "expires": "2020-05-05 08:00:00",
                "expired": false,
                "data": {
                    "tags": [
                        "user_uploads.u123",
                        "user_uploads"
                    ],
                    "allowedMimeTypes": [
                        "image\/jpeg",
                        "image\/png",
                        "image\/gif"
                    ],
                    "maxAllowedFileSize": 100,
                    "allowedIpAddresses": [],
                    "allowedUserAgents": [],
                    "secureCopyEncryptionKey": "",
                    "secureCopyEncryptionMethod": ""
                },
                "roles": [
                    "upload.images"
                ]
            },
            {
                "id": "dafe83fa-7813-4d84-a625-16c6657fec9f",
                "active": true,
                "data": {
                    "tags": [],
                    "allowedMimeTypes": [],
                    "maxAllowedFileSize": null,
                    "allowedIpAddresses": [],
                    "allowedUserAgents": [],
                    "secureCopyEncryptionKey": "",
                    "secureCopyEncryptionMethod": ""
                },
                "roles": [
                    "collections.create_new",
                    "collections.manage_tokens_in_allowed_collections"
                ]
            }
        ]
    }


Required roles:

    - security.search_for_tokens
    - security.authentication_lookup

Looking up a token
------------------

.. code:: json

    GET /auth/token/D0D12FFF-DD04-4514-8E5D-D51542DEBCFA?_token=your-admin-token-there

Example response:

.. code:: json

    {
        "status": true,
        "error_code": null,
        "http_code": 200,
        "errors": [],
        "context": [],
        "message": "Token found",
        "token": {
            "id": "ca6a2635-d2cb-4682-ba81-3879dd0e8a77",
            "active": true,
            "data": {
                "tags": [],
                "allowedMimeTypes": [],
                "maxAllowedFileSize": 0,
                "allowedIpAddresses": [],
                "allowedUserAgents": [],
                "secureCopyEncryptionKey": "",
                "secureCopyEncryptionMethod": ""
            },
            "roles": [
                "security.administrator",
                "upload.images",
                "upload.documents",
                "upload.backup",
                "upload.all",
                "security.authentication_lookup",
                "security.search_for_tokens",
                "security.overwrite",
                "security.generate_tokens",
                "security.use_technical_endpoints",
                "deletion.all_files_including_protected_and_unprotected",
                "view.any_file",
                "view.files_from_all_tags",
                "view.can_use_listing_endpoint_at_all",
                "security.revoke_tokens",
                "collections.create_new",
                "collections.create_new.with_custom_id",
                "collections.allow_infinite_limits",
                "collections.delete_allowed_collections",
                "collections.modify_any_collection_regardless_if_token_was_allowed_by_collection",
                "collections.modify_details_of_allowed_collections",
                "collections.view_all_collections",
                "collections.can_use_listing_endpoint",
                "collections.manage_tokens_in_allowed_collections",
                "collections.upload_to_allowed_collections",
                "collections.list_versions_for_allowed_collections",
                "collections.delete_versions_for_allowed_collections",
                "securecopy.stream",
                "securecopy.all_secrets_read"
            ]
        }
    }

Required roles:

    - security.authentication_lookup


Revoking a token
----------------

.. code:: json

    DELETE /auth/token/D0D12FFF-DD04-4514-8E5D-D51542DEBCFA?_token=your-admin-token-there

Example response:

.. code:: json

    {
        "status": true,
        "error_code": null,
        "http_code": 201,
        "errors": [],
        "context": [],
        "message": "Token was deleted",
        "token": {
            "id": null,
            "active": true,
            "expires": "2020-05-05 08:00:00",
            "expired": false,
            "data": {
                "tags": [],
                "allowedMimeTypes": [],
                "maxAllowedFileSize": 0,
                "allowedIpAddresses": [],
                "allowedUserAgents": [],
                "secureCopyEncryptionKey": "",
                "secureCopyEncryptionMethod": ""
            },
            "roles": [
                "security.administrator",
                "upload.images",
                "upload.documents",
                "upload.backup",
                "upload.all",
                "security.authentication_lookup",
                "security.search_for_tokens",
                "security.overwrite",
                "security.generate_tokens",
                "security.use_technical_endpoints",
                "deletion.all_files_including_protected_and_unprotected",
                "view.any_file",
                "view.files_from_all_tags",
                "view.can_use_listing_endpoint_at_all",
                "security.revoke_tokens",
                "collections.create_new",
                "collections.create_new.with_custom_id",
                "collections.allow_infinite_limits",
                "collections.delete_allowed_collections",
                "collections.modify_any_collection_regardless_if_token_was_allowed_by_collection",
                "collections.modify_details_of_allowed_collections",
                "collections.view_all_collections",
                "collections.can_use_listing_endpoint",
                "collections.manage_tokens_in_allowed_collections",
                "collections.upload_to_allowed_collections",
                "collections.list_versions_for_allowed_collections",
                "collections.delete_versions_for_allowed_collections",
                "securecopy.stream",
                "securecopy.all_secrets_read"
            ]
        }
    }


Required roles:

    - security.revoke_tokens

