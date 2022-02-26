Documentation
=============

Kubernetes resources
--------------------

Backup Repository server is designed to be Kubernetes-first and Security-first.
Configuration of basic entities such as **users** and **backup collections** are done using YAMLs in Kubernetes syntax.

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
# (...)

---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupUser
# (...)
```

**Check examples of Kubernetes YAMLs:**

- [`kind: BackupUser`](examples/user.yaml)
- [`kind: BackupCollection`](examples/collection.yaml)
- [`kind: Secret` (secrets referenced in above examples for `kind: BackupUser` and `kind: BackupCollection`)](examples/secret.yaml)

**Your server instance can be configured using those YAML's basically, the rest are highly dynamic changing data that is configured via API, it includes `Authentication keys` and `Uploaded backup versions that are rotating`**

API
---

Interactions with server are done using HTTP API that talks JSON in both ways, and identifies with JWT.

### [Users](api/users/README.md)

### Collections

### Administrative

