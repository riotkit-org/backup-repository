File Repository
===============

**Note: We are preparing for a renaming to Backup Repository with release v4. Features like object storage, pseudo-replication, html-forms for integration with applications will be no longer maintained and deleted from the codebase as we are no longer able to maintain a bigger project - so we picked a specialization which is a hosted multi-user backup storage. We also dropped support for MySQL, SQLite3 and FTP in v4, we will only support PostgreSQL + local filesystem/AWS S3/Min.io. Critical fixes and security fixes will be cherry-picked to 3.x branch for a period of one year.**

[![Build Status](https://travis-ci.org/riotkit-org/file-repository.svg?branch=master)](https://travis-ci.org/riotkit-org/file-repository)
[![Documentation Status](https://readthedocs.org/projects/file-repository/badge/?version=latest)](https://file-repository.docs.riotkit.org/en/latest/?badge=latest)
[![Maintainability](https://api.codeclimate.com/v1/badges/4ed37b276f5379c3dc52/maintainability)](https://codeclimate.com/github/riotkit-org/file-repository/maintainability)
[![codecov](https://codecov.io/gh/riotkit-org/file-repository/branch/master/graph/badge.svg)](https://codecov.io/gh/riotkit-org/file-repository)

File Repository is a modern API application dedicated for storing files. 
Can use any type of backend supported by Flysystem library, officialy we support S3 and local filesystem.
Lightweight, requires just PHP 7.4+ and at least SQLite3, PostgreSQL or MySQL.

#### For installation, guides and more information please check he documentation: https://file-repository.readthedocs.io/en/latest/index.html

**Main functionality:**

- Strict access control, you can **generate a token** that will have access to specific actions and access to specific items
- Store files where you need; on **AWS S3, Minio.io, local storage, or on other location supported by Flysystem library (we oficially test on local storage and Min.io)**
- **Deduplication for non-grouped files**. There will be no duplicated files stored on your disk
- **Backups management**, you can define a collection of file versions that can **rotate on adding a new version**
- HTTP API, download endpoints supports Bytes Range (rewinding video files), in-browser cache
- Ready to integrate upload forms for your applications. Only generate token and redirect a user to an url
- Encryption

**Requirements for the server:**
- PHP 7.4+ with bcmath, openssl, iconv, ctype, fileinfo, json, pdo, pdo_sqlite, pdo_pgsql, pdo_mysql
- Composer (PHP package manager)
- "file" standard unix shell command
- "sha256sum" unix shell command
- MariaDB 10.2+ / SQLite 3 / PostgreSQL 10.12+
- NodeJS 12.x + NPM (for building simple frontend at installation time)

**Requirements for the backup client "Bahub":**
- Python 3.6+
- For a list of required pip packages check: [requirements.txt](bahub-client/requirements.txt)
- PostgreSQL client tools (for PostgreSQL databases backup support)
- MariaDB/MySQL client tools (for MySQL/MariaDB databases backup support)

**Requirements to manually build documentation:**
- sphinx-glpi-theme
- sphinx

Copyleft
--------

Created by **RiotKit Collective**.
Project initially created for three purposes: 

- To store static files uploaded for users (libertarian/anarchist portal)
- To store and serve training video files (video archive)
- To store backups and version them. Limit the disk space and permissions to create a shared space for multiple organizations fighting for human-rights, tenants-rights, working-class rights and animals-rights.
