Configuration as a Code module
==============================

Stores application configuration in Kubernetes or in local filesystem as YAML files in Kubernetes syntax.
The state is synchronized in both ways.

Cache
-----

Cache layer should be implemented at adapter level and know the source object (fetched from configuration) and target object (saved back into configuration) to clear both from cache.
On each save, deletion, or external modification the cached object should be deleted.

Cache is a service independent of adapter, it is an implementation shared across adapters.


Filesystem adapter
------------------

Works in a directory structure that bases on object types and names.

**Pattern:**

```
strings.ReplaceAll(o.path+"/"+o.namespace+"/"+apiGroup+"/"+apiVersion+"/"+kind+"/"+id+".yaml", "//", "/")
```

**Examples:**

```
# Example: 
#    apiVersion: backups.riotkit.org/v1alpha1
#    kind: BackupUser
#    metadata:
#        name: admin
#        namespace: default
#

./default/backups.riotkit.org_v1alpha1/BackupUser/admin.yaml


# Example:
#   apiVersion: v1
#   kind: Secret
#   metadata:
#       name: backup-repository-passwords

./default/v1/Secret/backup-repository-passwords.yaml
```

#### Immutability

Like in Kubernetes `apiVersion`, `kind` and `metadata.name` are immutable, in this context - when any of them are changed, then object from application cache should be removed.

#### Labels

To be discussed how to implement.
