File Repository Server
======================

A Symfony application designed to store bigger amounts of data, including metadata attributes.

**Features:**

- Strict permissions (Role based)
- Tokens
- Upload forms ready to adapt for external application
- Limits per item, per backup collection
- Any kind of storage that is supported by Flysystem library (including AWS S3, Min.io, local filesystem, even FTP)
- Versioned backups

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
./vendor/bin/codecept run --html=/tmp/file-repository.html functional Features/Security/FeatureLimitTokenAccessPerIpAndUserAgentCest

# run API test case from root directory
./vendor/bin/codecept run --html=/tmp/file-repository.html AuthenticationCest
```

Copyleft
--------

Created by **RiotKit Collective**.
Project initially created for three purposes: 

- To store static files uploaded for users (libertarian/anarchist portal)
- To store and serve training video files (video archive)
- To store backups and version them. Limit the disk space and permissions to create a shared space for multiple organizations fighting for human-rights, tenants-rights, working-class rights and animals-rights.
