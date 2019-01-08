Creating a token
----------------

========================  =============================================================================================
   Parameters
------------------------  ---------------------------------------------------------------------------------------------
 name                      description
========================  =============================================================================================
roles                      A list of roles allowed for user. See permissions/configuration reference page
data.tags                  List of allowed tags to use in upload endpoints
data.allowedMimeTypes      List of allowed mime types
data.maxAllowedFileSize    Number of bytes of maximum file size
expires                    Expiration date, or "auto", "automatic", "never". Empty value means same as "auto"
========================  =============================================================================================

.. code:: json

    POST /auth/token/generate?_token=your-admin-token-there

    {
        "roles": ["collections.create_new", "collections.add_tokens_to_allowed_collections"],
        "data": {
            "tags": [],
            "allowedMimeTypes": ["image/jpeg", "image/png", "image/gif"],
            "maxAllowedFileSize": 14579
        },
        "expires": "2020-05-05 08:00:00"
    }

Example response:

.. code:: json

    {
        "tokenId": "D0D12FFF-DD04-4514-8E5D-D51542DEBCFA",
        "expires": "2019-01-07 06:30:43"
    }


Required roles:

    - security.generate_tokens



Looking up a token
------------------

.. code:: json

    GET /auth/token/D0D12FFF-DD04-4514-8E5D-D51542DEBCFA?_token=your-admin-token-there

Example response:

.. code:: json

    {
        "tokenId": "34A77B0D-8E6F-40EF-8E70-C73A3F2B3AF8",
        "expires": "2019-01-06 09:20:16",
        "roles": [
            "upload.images"
        ],
        "tags": [
            "user_uploads.u123",
            "user_uploads"
        ],
        "mimes": [
            "image/jpeg",
            "image/png",
            "image/gif"
        ],
        "max_file_size": 14579
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
        "tokenId": "D0D12FFF-DD04-4514-8E5D-D51542DEBCFA",
        "expires": "2019-01-07 06:30:43"
    }


Required roles:

    - security.revoke_tokens

