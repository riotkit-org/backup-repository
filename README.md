Backup Repository
=================

Specialized ninja for backup storage. Fully multi-tenant, can act as a farm of backups for various people.

Features
--------

- Fully **multi-tenant**, granular permissions and roles
- **End-To-End encryption**. Server acts as a blob storage, client is encrypting client-side
- Security focused, limit access by IP address, User Agent, limited scope API tokens. In future we may implement "scheduled backup windows" to prevent overwriting backups in short time periods
- Backups rotation strategies
- Very **low resources requirements**, a container with 256 MB ram and 0.5vCPU on a shared VM can fit
- Fully compatible with containerized workflows (**Docker supported** out-of-the-box by both client and server)
- Administrative **frontend in web browser**
- **JSON API**, JSON Web Token (JWT), SWAGGER documentation for the API

#### For installation, guides and more information please check the documentation: https://file-repository.readthedocs.io/en/latest/index.html

**Requirements for the server:**
- A Cheap VM - minimum of 0.5 vCPU, 256 MB ram (for about 10 backups each night)
- PHP 8.0+ with bcmath, iconv, ctype, fileinfo, json, pdo, pdo_sqlite, pdo_pgsql
- Composer (PHP package manager)
- "file" standard unix shell command
- "sha256sum" unix shell command
- PostgreSQL 10.12+

**Requirements for the backup client "Bahub":**
- Python 3.7+
- For a list of required pip packages check: [requirements.txt](bahub-client/requirements.txt)
- PostgreSQL client tools (for PostgreSQL databases backup support)
- MariaDB/MySQL client tools (for MySQL/MariaDB databases backup support)

**Requirements to build the administration frontend from sources:**
- NPM 6+
- NodeJS v15+

**Requirements to manually build documentation:**
- sphinx-glpi-theme
- sphinx

Developers
----------

Technically this repository consists of 3 applications placed in following directories:
- ./server - server written in PHP
- ./frontend - web administration panel written in Vue.js
- ./bahub-client - backup sending/downloading client written in Python

Check README.md of each of those projects to check technical details.
Please report issues with suffixes in topic \[Server] \[Frontend] \[Bahub].

Copyleft
--------

Created by **RiotKit Collective**.
Project initially created for three purposes: 

- To store static files uploaded for users (libertarian/anarchist portal)
- To store and serve training video files (video archive)
- To store backups and version them. Limit the disk space and permissions to create a shared space for multiple organizations fighting for human-rights, tenants-rights, working-class rights and animals-rights.
