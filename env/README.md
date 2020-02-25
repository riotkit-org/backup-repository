Developer environment
=====================

**Setting up the development environment:**

```bash
# setup a basic environment on SQLite3, with local storage driver
make up

# setup an environment with PostgreSQL database
make up VARIANT="test postgres"

# setup an environment with Min.io as storage through S3 interface
make up VARIANT="test s3"

# setup a mixed environment of File Repository on S3 + PostgreSQL
make up VARIANT="test postgres s3"
```

**Moving to production:**

You are probably looking for a working example how to setup the File Repository on S3 or PostgreSQL - we already
prepared a tool to provide you a docker-compose file.

Use a below command to generate a docker-compose example file, that will work as an example how to configure File Repository to work with eg. S3 or PostgreSQL.

```bash
make print VARIANT="gateway postgres s3 persistent"
```

**Running File Repository server API tests:**

```bash
# at first you need to run the environment
make up VARIANT="test postgres s3"

# run tests
make test_api

# filter tests
make test_api CODECEPT_ARGS="functional AuthenticationCest"
```

**Running File Repository server unit tests:**

```bash
make up VARIANT="test postgres s3"
make test_unit

# single tests run
make test_unit PHPUNIT_ARGS="--filter GenerateAdminTokenCommandTest"
```
