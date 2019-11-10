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

#### For installation, guides and more information please check he documentation: https://file-repository.readthedocs.io/en/latest/index.html

**Main functionality:**

- Strict access control, you can **generate a token** that will have access to specific actions on specific items
- Store files where you need; on **AWS S3, Minio.io, FTP, local storage and others...**
- **Deduplication for non-grouped files**. There will be no duplicated files stored on your disk
- **Backups management**, you can define a collection of file versions that can **rotate on adding a new version**
- API + lightweight frontend
- Ready to integrate upload forms for your applications. Only generate token and redirect a user to an url

**Requirements for the server:**
- PHP 7.2+ with bcmath, openssl, iconv, ctype, fileinfo
- Composer (PHP package manager)
- sphinx-glpi-theme (for documentation)
- sphinx (for documentation)
- file
- sha256sum
- MySQL 5.7+ / SQLite 3 / PostgreSQL (not officially supported but may work)

**Requirements for the backup client "Bahub":**
- Python 3.5+
- For a list of required pip packages check: [requirements.txt](bahub-client/requirements.txt)

Copyleft
--------

Created by **RiotKit Collective** as part of Wolnościowiec initiative.
Project initially created for three purposes: 

- To store static files uploaded for users (libertarian/anarchist portal)
- To store and serve training video files (video archive)
- To store backups and version them. Limit the disk space and permissions to create a shared space for multiple organizations fighting for human-rights, tenants-rights, working-class rights and animals-rights.
