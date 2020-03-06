File Repository
===============

[![Build Status](https://travis-ci.org/riotkit-org/file-repository.svg?branch=master)](https://travis-ci.org/riotkit-org/file-repository)
[![Documentation Status](https://readthedocs.org/projects/file-repository/badge/?version=latest)](https://file-repository.docs.riotkit.org/en/latest/?badge=latest)
[![Maintainability](https://api.codeclimate.com/v1/badges/4ed37b276f5379c3dc52/maintainability)](https://codeclimate.com/github/riotkit-org/file-repository/maintainability)
[![codecov](https://codecov.io/gh/riotkit-org/file-repository/branch/master/graph/badge.svg)](https://codecov.io/gh/riotkit-org/file-repository)

File Repository is a modern API application dedicated for storing files. 
Can use any type of backend supported by Flysystem library, officialy we support S3 and local filesystem.
Lightweight, requires just PHP 7.4+ and at least SQLite3 or MySQL (other databases can be also supported in future due to using ORM).

#### For installation, guides and more information please check he documentation: https://file-repository.readthedocs.io/en/latest/index.html

**Main functionality:**

- Strict access control, you can **generate a token** that will have access to specific actions on specific items
- Store files where you need; on **AWS S3, Minio.io, FTP, local storage and others...**
- **Deduplication for non-grouped files**. There will be no duplicated files stored on your disk
- **Backups management**, you can define a collection of file versions that can **rotate on adding a new version**
- API + lightweight frontend
- Ready to integrate upload forms for your applications. Only generate token and redirect a user to an url

**Requirements for the server:**
- PHP 7.4+ with bcmath, openssl, iconv, ctype, fileinfo, json, pdo, pdo_sqlite, pdo_pgsql, pdo_mysql
- Composer (PHP package manager)
- "file" standard unix shell command
- "sha256sum" unix shell command
- MariaDB 10.2+ / SQLite 3 / PostgreSQL 10.12+
- NodeJS 12.x + NPM (for building simple frontend at installation time)

**Requirements for the backup client "Bahub":**
- Python 3.5+
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
