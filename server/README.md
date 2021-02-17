Backup Repository Server
========================

Developing
----------

```bash
# run dependencies - database, cache
rkd :docker:up

# generate required keys for JWT authorization
rkd :create:keys

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

# run API tests - by group
./vendor/bin/codecept run --html=file-repository.html -g Domain/Backup

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
