File Repository
===============

[![Build Status](https://travis-ci.org/riotkit-org/file-repository.svg?branch=master)](https://travis-ci.org/riotkit-org/file-repository)
[![Documentation Status](https://readthedocs.org/projects/file-repository/badge/?version=latest)](https://file-repository.docs.riotkit.org/en/latest/?badge=latest)

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

Testing
-------

Server is tested by API tests and unit tests.
Bahub client is tested with integration tests written in bash and docker, and unit tests.

Copyleft
--------

Created by **RiotKit Collective** as part of Wolnościowiec initiative.
Project initially created for three purposes: 

- To store static files uploaded for users (libertarian/anarchist portal)
- To store and serve video files (video archive)
- To store backups and version them. Limit the disk space and permissions to create a shared space for multiple organizations fighting for human-rights, tenants-rights, working-class rights and animals-rights.
