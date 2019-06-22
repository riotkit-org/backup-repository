Authorization
=============

File Repository is an API application, so there is no user account identified by login and password, there are **ACCESS TOKENS**.

An access token is identified by long UUIDv4, and has assigned information about the access, such as:

- List of actions that are allowed (eg. file uploads could be allowed, but browsing the list of files not)
- Allowed tags that could be used when uploading
- Allowed file types (mime types) when uploading
- Maximum allowed file size
- Token expiration date


To authorize in the API you need to provide the token in one of those methods:
- Using a query parameter "_token" eg. /some/url?_token=123
- Using a HTTP header "X-Auth-Token"

.. toctree::
   :maxdepth: 2
   :caption: Contents:

   operating-tokens
