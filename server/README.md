Backup Repository Server
========================

An application designed for shared backups storage with limits per user, per application and per file.
Perfectly fits in case, when multiple organizations are hosting their backups at same server - the administrator can allocate space and grant rights.

*Written in PHP 7.4 & Symfony 5.x, designed to run on cheap hardware and on cheap cloud infrastructure.*

**Features:**

- Strict permissions (Role based)
- Users management
- Limits per item, per backup collection, per user
- Storage at the local filesystem or at AWS S3 compatible storage (officially supporting Min.io)
- Versioned backups with rotation policies

Developing
----------

```bash
# run dependencies
rkd :docker:up

# migrate database
./bin/console doctrine:migrations:migrate -vv

# run development web server (requires a Symfony CLI utility to be installed and in PATH)
symfony serve

#
# Testing
#

# run all API test cases
./vendor/bin/codecept run

# run API test case from selected directory
./vendor/bin/codecept run --html=file-repository.html functional Features/Security/FeatureLimitTokenAccessPerIpAndUserAgentCest

# run API test case from root directory
./vendor/bin/codecept run --html=file-repository.html functional AuthenticationCest

# run API test - single test
./vendor/bin/codecept run --html=file-repository.html functional AuthenticationCest:generateBasicToken

# unit tests (all tests)
./vendor/bin/phpunit

# single unit test
./vendor/bin/phpunit --filter 
```

Copyleft
--------

Created by **RiotKit Collective**.
Project initially created for three purposes: 

- To store static files uploaded for users (libertarian/anarchist portal)
- To store and serve training video files (video archive)
- To store backups and version them. Limit the disk space and permissions to create a shared space for multiple organizations fighting for human-rights, tenants-rights, working-class rights and animals-rights.
