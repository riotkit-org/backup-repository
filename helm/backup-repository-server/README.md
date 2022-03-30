Backup Repository
=================

[![Coverage Status](https://coveralls.io/repos/github/riotkit-org/backup-repository/badge.svg?branch=main)](https://coveralls.io/github/riotkit-org/backup-repository?branch=main)
[![Test](https://github.com/riotkit-org/backup-repository/actions/workflows/test.yaml/badge.svg)](https://github.com/riotkit-org/backup-repository/actions/workflows/test.yaml)
[![Artifact Hub](https://img.shields.io/endpoint?url=https://artifacthub.io/badge/repository/riotkit-org)](https://artifacthub.io/packages/search?repo=riotkit-org)

Cloud-native, zero-knowledge, multi-tenant, security-first backup storage with minimal footprint.

_TLDR; Primitive backup storage for E2E GPG-encrypted files, with multi-user, quotas, versioning, using a object storage (S3/Min.io/GCS etc.) and deployed on Kubernetes or standalone. No fancy stuff included, lightweight and stable as much as possible is the project target._

```bash
helm repo add riotkit-org https://riotkit-org.github.io/helm-of-revolution/
helm install backups riotkit-org/backup-repository-server -n backup-repository
```

Documentation
-------------

### [For documentation please look at Github](https://github.com/riotkit-org/backup-repository/blob/main/docs/README.md)

**NOTICE:** Please consider selecting a versioned tag from branch/tag selector.
