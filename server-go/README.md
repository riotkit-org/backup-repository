Backup Repository
=================

Cloud-native, zero-knowledge, multi-tenant backup storage with minimal footprint.

**Natively supports:**
- Kubernetes (but does not require)
- GPG E2E encryption
- Configuration via GitOps (Configuration as a Code)


Security
--------

- For authentication JSON Web Token was used
- Tokens are long-term due to usage nature
- All JWT's can be revoked anytime. There is a list of generated tokens stored in configuration (only sha256 shortcuts)
- Passwords are encoded with `argon2di` (winner of the 2015 Password Hashing Competition, recommended by OWASP)

```go
Argon2Config{
    time:    1,
    memory:  64 * 1024,
    threads: 4,
    keyLen:  32,
}
```
