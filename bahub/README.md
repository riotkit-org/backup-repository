Bahub - shell backup client
===========================

Backups made easy, automated, monitored.

```bash
# common flows
bahub :backup:make my_db
bahub :backup:restore my_db --version=v1
bahub :backup:restore my_db # latest

# inline docs
bahub :help:info               # Lists all built-in backup types and transports
bahub :help:transport:example  # Shows example transport configuration for given transport
bahub :help:backup:example     # Shows a example configuration for a given backup type
```

```yaml
accesses:
    my_local_instance:
        url: "http://localhost:8000"
        token: "${API_TOKEN}"  # example of reading a variable from ENVIRONMENT

encryption:
    my_key_pair_one:
        gnupg_home: "~/.bahub-gnupg"
        passphrase: "capitalism-must-end"
        method: "aes256"
        username: "Mikhail Bakunin"
        email: "bakunin@example.org"

transports:
    my_docker_mysql_container:
        type: bahub.transports.docker  # Please notice: Transport name is a Python package path, unofficial transports are welcome!
        spec:
            container: "bahub_adapter_integrations_db_mysql_1"

backups:
    db_mysql:
        meta:
            type: bahub.adapters.mysql   # Please notice: This is a Python package path, unofficial adapters are welcome!
            access: my_local_instance    # Please notice: You can backup to multiple remote servers
            encryption: my_key_pair_one  # Please notice: You can encrypt with different keys as you wish
            collection_id: "11111-2222-33333-4444" # collection id created on Backup Repository server (optional when saving backup to file only)
            transport: my_docker_mysql_container  # Please notice: You can execute the backup EVERYWHERE
        spec:
            host: "my-database-host.local"
            port: 3306
            user: "root"
            password: "root"
            #database: "example" # when "database" is not specified, the all databases will be dumped
```

**Features:**
- **End-To-End encryption** using GNU Privacy Guard, you can store your backups on a remote server that will not know what you store
- Natively sends backups to **Backup Repository server**, or stores into files
- Backups made with just a one **simple** command `bahub :backup:make my-db`
- Restoring is as simple as backup `bahub :backup:restore my-db`
- **Does not require additional disk space to store backup**, the backup is done on-the-fly
- **Natively supports Docker**, including databases running in Docker
- Understands what is to back up by using native methods such as `mysqldump`, `pg_dump` and others depending on what application is it
- Supports "offline backup" of Docker containers by turning them off, then copying the data
- Slack/Mattermost notifications about successes and failures
- Errors monitoring with Sentry.io support

**Abstract architecture:**
- Adapters like `mysql`, `postgres_dump`, `filesystem` are defining how to properly do your backup, there can be many more adapters, even made by external people all around the world
- Transports: We support executing backup in `sh` (local shell), `docker` (docker container), `temporarydocker` (offline, copying files of other docker container), but feel free to write your own transport or use transport written by other people. There are many possibilities such as enabling `SSH`, `Kubernetes`, `ECS` and more.

**Extensible:**
- Although we do not support currently Kubernetes or remote backups via SSH it does not mean
  that it is impossible - Bahub is extensible, everyone can write an adapter that will enable `Kubernetes`, `ECS`, `SSH`
  or any other transport that can run commands and return output
- Bahub can use backup adapters and command transports that are placed in other Python packages, so **any unofficial adapters and transports are easily pluggable!**

Requirements
------------

- Linux machine when installed directly on host machine, any other os if running on Docker (including Windows)
- Python 3.9 (if you cannot afford to install it on your old CentOS, then use Docker)
- MySQL client tools (if going to backup & restore MySQL databases)
- PostgreSQL client tools (if going to backup & restore PostgreSQL databases)
- GNU tar
- GNU Privacy Guard 2.x (mandatory, for E2E encryption support. There is no way to turn off encryption)

Installing
----------

a) via Python package (on host machine)

```bash
pip install bahub
```

b) via Docker (in a container)

```bash
# todo
```

Development
-----------

```bash
export RKD_SYS_LOG_LEVEL=debug
python -m bahub -rl debug :SOME-TASK-HERE --config=./bahub.conf.yaml
```
