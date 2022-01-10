Backup Repository
=================

A specialized ninja for backup making - complete backups ecosystem. Fully multi-tenant, with its very granular permissions and client-side (E2E) encryption can act as a farm of backups for various people and organizations.

[![Build Status](https://travis-ci.org/riotkit-org/file-repository.svg?branch=master)](https://travis-ci.org/riotkit-org/backup-repository)
[![Documentation Status](https://readthedocs.org/projects/file-repository/badge/?version=latest)](https://file-repository.docs.riotkit.org/en/latest/?badge=latest)
[![Maintainability](https://api.codeclimate.com/v1/badges/4ed37b276f5379c3dc52/maintainability)](https://codeclimate.com/github/riotkit-org/backup-repository/maintainability)
[![codecov](https://codecov.io/gh/riotkit-org/file-repository/branch/master/graph/badge.svg)](https://codecov.io/gh/riotkit-org/backup-repository)

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
- On client side the backups are made on-the-fly, you don't need extra disk space for temporary storage

#### For installation, guides and more information please check the documentation: https://file-repository.readthedocs.io/en/latest/index.html

**Requirements for the server:**
- A Cheap VM - minimum of 0.5 vCPU, 256 MB ram (for about 10 backups each night, but it depends on many factors, you need to check it and adjust by yourself before using on production)
- PHP 8.0+ with bcmath, iconv, ctype, fileinfo, json, pdo, pdo_sqlite, pdo_pgsql
- Composer (PHP package manager)
- "file" standard unix shell command
- "sha256sum" unix shell command
- PostgreSQL 10.12+

**Requirements for the backup client "Bahub":**
- Python 3.7+
- For a list of required pip packages check: [requirements.txt](bahub/requirements.txt)
- PostgreSQL client tools (for PostgreSQL databases backup support)
- MariaDB/MySQL client tools (for MySQL/MariaDB databases backup support)

**Requirements to build the administration frontend from sources:**
- NPM 6+
- NodeJS v15+

**Requirements to manually build documentation:**
- sphinx-glpi-theme
- sphinx

Developing
----------

Technically this repository consists of 3 applications + functional tests placed in following directories:
- ./server - server written in PHP
- ./frontend - web administration panel written in Vue.js
- ./bahub - backup sending/downloading client written in Python
- ./e2e - End-To-End integration tests that are testing server + frontend + client

Check README.md of each of those projects to check technical details.
Please report issues with suffixes in topic [Server] [Frontend] [Bahub].

Tests
-----

## E2E - integration tests

Smoke testing of workflows that includes multiple applications, multiple interfaces together.
The tests are using Chromium browser to test frontend, are launching backup client via shell, Symfony shell commands on the server side.

Tests are written in **Gherkin/Cucumber** language with addition of PHP on top of **Behat 3.x** framework.

**Note:** .feature files in E2E tests are English written scenarios that should be clear to the end user, so are also an instruction/specification of given feature - can be used as a documentation

**E2E tests on CI are performed on production mode, using a production-like container**

```bash
cd e2e
./vendor/bin/behat
```

**Notice: Requires server to be up and running on `localhost:8000` in `APP_ENV=test` mode (uses technical endpoints and test token)**

## Server unit tests

Unit tests written in PhpUnit.

**Unit tests on CI are performed on CI on host**

```bash
cd server
./bin/phpunit
```

## Server API tests

Functional API tests written in Codeception. Requires a server to be up and running under `localhost:8000` in `APP_ENV=test` mode.

**API tests on CI are performed on production mode, using a production-like container**

## Bahub unit/functional tests

Unit and functional tests written in `unittest` + `rkd.api.testing` frameworks.

**Unit and functional tests on CI are performed on CI on host**

```bash
cd bahub
rkd :test
```

Release versioning and naming
-----------------------------

Each release is versioned according to Semantic Versioning and named.
Names are taken from profession names to commemorate and express respect to forgotten professions.

Copyleft
--------

Created by **RiotKit Collective**.
Project created to provide a zero-knowledge backup storage using strong encryption, with granular permissions management and advanced multi-tenancy.
We use it to support multiple libertarian collectives across the world by providing a mechanism, without knowing what data is stored for our and their security.
