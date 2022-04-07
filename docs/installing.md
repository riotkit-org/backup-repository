Installing
==========

In Kubernetes
-------------

Before you begin make sure you have PostgreSQL and object storage.

We recommend those Helm Charts for PostgreSQL and object storage if you do not have one:
- [PostgreSQL](https://artifacthub.io/packages/helm/bitnami/postgresql)
- [Min.io](https://artifacthub.io/packages/helm/minio/minio)

Use Helm to import and install Backup Repository Helm Chart.

```bash
helm repo add riotkit-org https://riotkit-org.github.io/helm-of-revolution/
helm upgrade --install backups riotkit-org/backup-repository-server -n backup-repository --values values.yaml
```

Example `values.yaml` file:

```yaml
secrets:
    BR_JWT_SECRET_KEY: "87GHq66A+uGkcn/AyxrnPYdd5F0XUmGlHsREbY3tcM4CpO6/dFL7z/057DHnp9nMdoYOpKxwYWrM9XyffjrBidm6/VCzfam9GwMlOac7TsidcTnSHG5IasPICb9bKE3h" # MANDATORY
    BR_DB_HOSTNAME: "postgres-postgresql.backup-repository.svc.cluster.local"
    BR_DB_PASSWORD: "putinchuj"
    BR_DB_USERNAME: "riotkit"
    BR_DB_NAME: "backup-repository"
    BR_DB_PORT: "5432"

env:
    AWS_SECRET_KEY: "wJaFuCKtnFEMI/CApItaliSM/bPxRfiCYEXAMPLEKEY"
    AWS_ACCESS_KEY_ID: "AKIAIOSFODNN7EXAMPLE"
    GIN_MODE: debug

ingress:
    enabled: true
    className: ""
    annotations: {}
    hosts:
        - host: backup-repository.example.org
          paths:
              - path: /
                pathType: ImplementationSpecific
```

Bare-metal, no Docker, no Kubernetes
------------------------------------

**Requirements:**
- PostgreSQL
- Min.io or cloud storage

### Setting up Min.io

Check [Min.io quickstart](https://docs.min.io/minio/baremetal/#quickstart) for the instructions on how to prepare the storage instance.

### Setting up PostgreSQL

1. [Install PostgreSQL](https://www.postgresql.org/docs/14/install-binaries.html)
2. [Configure pg_hba.conf](https://www.postgresql.org/docs/14/auth-pg-hba-conf.html) to make sure you can login using IP address instead of UNIX socket (Backup Repository uses TCP/IP connection mode)
3. [Create database](https://www.postgresql.org/docs/14/manage-ag-createdb.html)

**Alternatively if you would like to use Docker to set up just the PostgreSQL, its easier:**

```bash
docker run -d \
    --name br_postgres \
    -e POSTGRES_PASSWORD=postgres \
    -e POSTGRES_USER=postgres \
    -e POSTGRES_DB=postgres \
    -e PGDATA=/var/lib/postgresql/data/pgdata \
    -v $$(pwd)/postgres-data:/var/lib/postgresql \
    -p 5432:5432 \
    postgres:14.1-alpine
```

### Setting up Backup Repository

1. Download `backup-repository` binary from [Releases tab](https://github.com/riotkit-org/backup-repository/releases) for selected stable version.

2. Prepare configuration directory

Your configuration directory needs to have a proper structure. Every file is expected to be at given path according to the following pattern:

```bash
# for Backup Repository resources 
{.metadata.namespace}/{.apiGroup}/{.apiVersion}/{kind}/{.metadata.name}.yaml

# for Secrets and ConfigMaps
{.metadata.namespace}/{.apiVersion}/{kind}/{.metadata.name}.yaml
```

Example structure:

```
└── backup-repository
    ├── backups.riotkit.org
    │         └── v1alpha1
    │             ├── backupcollections
    │             │         └── iwa-ait.yaml
    │             └── backupusers
    │                 ├── admin.yaml
    │                 ├── some-user.yaml
    │                 └── unprivileged.yaml
    └── v1
        └── secrets
            ├── backup-repository-collection-secrets.yaml
            └── backup-repository-passwords.yaml
```

4. Run unpacked binary

```bash
# min.io credentials
export AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE;
export AWS_SECRET_ACCESS_KEY=wJaFuCKtnFEMI/CApItaliSM/bPxRfiCYEXAMPLEKEY;
	
./backup-repository \
    --db-hostname=127.0.0.1 \
    --db-port=5432 \
    --db-password=postgres \
    --db-user=postgres \
    --db-password=postgres \
    --db-name=postgres \
    --health-check-key=changeme \
    --jwt-secret-key="secret key" \
    --storage-io-timeout="5m" \
    --listen=":8080" \
    --provider=filesystem \
    --config-local-path=./my-config-directory \
    --storage-url="s3://mybucket?endpoint=localhost:9000&disableSSL=true&s3ForcePathStyle=true&region=eu-central-1"
```
