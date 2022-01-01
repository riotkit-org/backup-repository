Backup Maker
============

Tiny backup client packed in a single binary. Interacts with a `Backup Repository` server to store files, uses GPG to secure your
backups even against the server administrator.

**Features:**
- Captures output from user-defined Backup/Restore commands
- Automated, optional GPG support enables easy to use E2E encryption
- Buffered upload of backup made on-the-fly requires no additional disk space to create backup
- Small, single binary, can be injected into container or distributed as a lightweight container

**Notice:** You need to have backup of your encryption private key. **Lost encryption key means your backups are unreadable!**

# Usage

## Creating backup

```bash
# most of commandline switches can be replaced with environment variables, check the table in other section of documentation
export BM_AUTH_TOKEN="some-token"; \
export BM_COLLECTION_ID="111-222-333-444"; \
export BM_PASSPHRASE="riotkit"; \
backup-maker make --url https://example.org \
    -c "tar -zcvf - ./" \
    --key build/test/backup.key \
    --recipient test@riotkit.org \
    --log-level info
```

## Restoring a backup

```bash
# commandline switches could be there also replaced with environment variables
backup-maker restore --url $$(cat .build/test/domain.txt) \
    -i $$(cat .build/test/collection-id.txt) \
    -t $$(cat .build/test/auth-token.txt) \
    -c "cat - > /tmp/test" \
    --private-key .build/test/backup.key \
    --passphrase riotkit \
    --recipient test@riotkit.org \
    --log-level debug
```

## Hints

- Skip `--private-key` and `--passphrase` to disable GPG
- Use `debug` log level to see GPG output and more verbose output at all


## Proposed usage

### Scenario 1: Standalone binary running from crontab

Just schedule a cronjob that would trigger `backup-maker make` with proper switches. Create a helper script to easily restore backup as a part
of a disaster recovery plan.

### Scenario 2: Dockerized applications, keep it inside application container

Pack `backup-maker` into docker image and trigger backups from internal or external crontab, jobber or other scheduler.

### Scenario 3: Use with `backup-controller` in Kubernetes

`backup-controller` acts as a scheduler, improving security and adding automation.

1. Create token in `Backup Repository` that will have a possibility to generate temporary tokens for single-time file uploads
2. Assign token to `backup-controller` (use it in configuration file)
3. According to `backup-controller` documentation: Setup `Kubernetes` transport, so it will inject binary or run a `kind: Job`

### Scenario 4: Use with `backup-controller` in Docker/Docker-Compose

`backup-controller` acts as a scheduler, improving security and adding automation.

1. Create token in `Backup Repository` that will have a possibility to generate temporary tokens for single-time file uploads
2. Assign token to `backup-controller` (use it in configuration file)
3. According to `backup-controller` documentation: Setup `Docker` transport, it will inject binary into existing application container or run a temporary container with attached application volumes

### Scenario 5: Use with ArgoCD Workflows

Create a definition of a ArgoCD Workflow that will spawn a Kubernetes job with defined token, collection id, command, GPG key.

Environment variables
---------------------

Environment variables are optional, if present will cover values of appropriate commandline switches.

| Type    | Name                | Description                                                                               |
|---------|---------------------|-------------------------------------------------------------------------------------------|
| path    | BM_PUBLIC_KEY_PATH  | Path to the public key used for encryption                                                |
| string  | BM_CMD              | Command used to encrypt or decrypt (depends on context)                                   |
| string  | BM_PASSPHRASE       | Passphrase for the GPG key                                                                |
| string  | BM_VERSION          | Version to restore (defaults to "latest"), e.g. v1                                        |
| email   | BM_RECIPIENT        | E-mail address of GPG recipient key                                                       |
| url     | BM_URL              | Backup Repository URL address e.g. https://example.org                                    |
| uuidv4  | BM_COLLECTION_ID    | Existing collection ID                                                                    |
| jwt     | BM_AUTH_TOKEN       | JSON Web Token generated in Backup Repository that allows to write to given collection id |
| integer | BM_TIMEOUT          | Connection and read timeouts in seconds                                                   |
| path    | BM_PRIVATE_KEY_PATH | GPG private key used to decrypt backup                                                    |
