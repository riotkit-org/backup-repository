SecureCopy API endpoints
========================

Copying mechanism exposes a list of files with a pagination/buffer, and a download endpoint.

The data in download endpoint can be encrypted or not, it depends on who is actually requesting the data.
If a person with token, that has encryption enabled is requesting a file, then it will be encrypted. The person does not need
to know the token, it will only receive a black-box data without having a knowledge what's inside.

======================================  ====================================================================================================================
 **Roles** used by the endpoints
------------------------------------------------------------------------------------------------------------------------------------------------------------
 name                                    description
======================================  ====================================================================================================================
securecopy.stream                        Can stream list of all files in storage, it's metadata and file content (in encrypted or not encrypted form)
securecopy.all_secrets_read              (Administrative) Can read all tokens encryption secrets
======================================  ====================================================================================================================

