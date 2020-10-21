Authorization
=============

Multiple tokens with different permissions could be assigned to the single collection.
You may create a token for uploading backups, deleting backups and for managing collection limits separately.

.. _granting_access_to_collection:

Assigning a token to the collection
-----------------------------------

.. code:: json

    POST /repository/collection/{{collection_id}}/token?_token={{collection_management_token}}

    {
        "token": "SO-ME-TO-KEN-TO-ADD"
    }


Legend:

    - {{collection_management_token}} is your token that has access rights to fully manage collection
    - {{collection_id}} is an identifier that you will receive on collection creation (see collection creation endpoint)

Required permissions:

    - collections.manage_users_in_allowed_collections

Revoking access to the collection for given token
-------------------------------------------------

.. code:: json

    DELETE /repository/collection/{{collection_id}}/token/{{token_id}}?_token={{collection_management_token}}


Legend:

    - {{token_id}} identifier of a token that we want to disallow access to the collection
    - {{collection_management_token}} is your token that has access rights to fully manage collection
    - {{collection_id}} is an identifier that you will receive on collection creation (see collection creation endpoint)

Required permissions:

    - collections.manage_users_in_allowed_collections
