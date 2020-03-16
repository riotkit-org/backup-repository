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

What is encrypted or hashed?
----------------------------

There are multiple elements that are hidden for the user, who is the client:

- Element-id/file-id is hashed using digest algorithm eg. sha512 (**Notice: salt and multiple rounds are not used there**)
- Form is encrypted using AES, it contains a form that would need to be submitted if we would like to import the data
- File content is encrypted using AES


Specification of data format returned by event list endpoint
============================================================

- Secure Copy event list is returning a list of JSON documents.
- The first line is a header line with information about server endpoints.
- After header there is a separator "\n\n" (two new lines), that separates headers from body.
- Each body line is a separate entry/file

**Example data returned by listing endpoint:**

.. code:: json

    {"metadataUrlTemplate":"http:\/\/localhost\/secure-copy\/file\/%file_id\/submitdata?_token=%token%","fetchUrlTemplate":"http:\/\/localhost\/secure-copy\/file\/%file_id\/submitdata?_token=%token%","remainingSince":3}

    {"type":"file","id":"8bf3e1c74cpng.png","date":1584342805,"tz":"UTC","form":{"backUrl":"","fileOverwrite":false,"tags":["test"],"password":"","public":true,"fileName":"8bf3e1c74cpng.png","stripInvalidCharacters":false}}
    {"type":"file","id":"5ab87e338bstorage.sql.gz","date":1584342847,"tz":"UTC","form":{"backUrl":"","fileOverwrite":false,"tags":["test"],"password":"","public":true,"fileName":"5ab87e338bstorage.sql.gz","stripInvalidCharacters":false}}
    {"type":"file","id":"0b8eeb2c61aaaaaaa","date":1584342904,"tz":"UTC","form":{"backUrl":"","fileOverwrite":false,"tags":[],"password":"","public":false,"fileName":"0b8eeb2c61aaaaaaa","stripInvalidCharacters":false}}


======================================  =========================  ====================================================================================================================
 Fields reference
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 name                                    Can be encrypted/hidden
======================================  =========================  ====================================================================================================================
 type                                    No                         Secure Copy element type, supported: file
 id                                      Yes - hashed               File id (element id - secure copy in future will support not only files, but also tokens and other data)
 date                                    No                         UNIX timestamp of the element. **The list is sorted by this column, order is important as we go through timeline!**
 tz                                      No                         Timezone in plaintext representation eg. UTC, Europe/Warsaw
 form                                    Yes - using AES            Form that needs to be submitted in order to push that data back to File Repository (eg. re-upload a file)
======================================  =========================  ====================================================================================================================
