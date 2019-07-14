File Repository
===============

[![Build Status](https://travis-ci.org/riotkit-org/file-repository.svg?branch=master)](https://travis-ci.org/riotkit-org/file-repository)
[![Documentation Status](https://readthedocs.org/projects/file-repository/badge/?version=latest)](https://file-repository.docs.riotkit.org/en/latest/?badge=latest)
[![Maintainability](https://api.codeclimate.com/v1/badges/4ed37b276f5379c3dc52/maintainability)](https://codeclimate.com/github/riotkit-org/file-repository/maintainability)
[![codecov](https://codecov.io/gh/riotkit-org/file-repository/branch/master/graph/badge.svg)](https://codecov.io/gh/riotkit-org/file-repository)
[![Docker Repository on Quay](https://quay.io/repository/riotkit/file-repository/status "Docker Repository on Quay")](https://quay.io/repository/riotkit/file-repository)

File Repository is a modern API application dedicated for storing files.
It is able to use various storage backends including AWS S3, Dropbox, Google Drive and just filesystem.
Lightweight, requires just PHP7 and at least SQLite3 or MySQL (other databases can be also supported in future due to using ORM).

See the documentation there: https://file-repository.readthedocs.io/en/latest/index.html

Main functionality:

- Strict access control, you can **generate a token** that will have access to specific actions on specific items
- Store files where you need; on **AWS S3, Minio.io, Dropbox, Google Drive, FTP, SFTP, and others...**
- **Deduplication for non-grouped files**. There will be no duplicated files stored on your disk
- **Backups management**, you can define a collection of file versions that can **rotate on adding a new version**
- Pure API, you can choose any frontend, use it internally in your application, or create your own frontend

Requirements:
- PHP 7.2+ with bcmath, openssl, iconv, ctype, fileinfo
- Composer (PHP package manager)
- sphinx-glpi-theme (for documentation)
- sphinx (for documentation)
- file
- sha256sum
- MySQL 5.7+ / SQLite 3 / PostgreSQL (not officially supported but may work)

Docker
------

```
# server
docker pull quay.io/riotkit/file-repository

# server + sentry.io integration
docker pull quay.io/riotkit/file-repository-sentry

# backups shell client, it's main target is to work as an backup automation on production servers
docker pull quay.io/riotkit/bahub
```

Testing
-------

Server is tested by API tests and unit tests.
Bahub client is tested with integration tests written in bash and docker, and unit tests.

Starting the development
------------------------

```
git clone git@github.com:riotkit-org/file-repository.git
cd file-repository

make develop
make run_dev

xdg-open http://localhost:8000
```

Versioning
----------

The application is using GITVER tool to keep strict versioning.

- A release should be made with a `make release`
- When starting working on a next known release number then use `make version_set_next`

Copyleft
--------

Created by **RiotKit Collective** as part of Wolnościowiec initiative.
Project initially created for three purposes: 

- To store static files uploaded for users (libertarian/anarchist portal)
- To store and serve video files (video archive)
- To store backups and version them. Limit the disk space and permissions to create a shared space for multiple organizations fighting for human-rights, tenants-rights, working-class rights and animals-rights.
