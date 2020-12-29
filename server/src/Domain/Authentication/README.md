Authentication Domain
=====================

Entity: User
------------

User account, identified by UUIDv4 and e-mail. Authentication is via e-mail and password, in result a JSON Web Token is generated.

Entity: AccessTokenAuditEntry
-----------------------------

Every time the user is logging in, or generating an API token - the JWT token is recorded and stored as a hash + shortcut (first 16 characters + last 16 characters).

**Advantages of storing access tokens:**
- Possibility to revoke any token
- List all active user sessions and API tokens used by applications
- Know when token was generated, with which scope
