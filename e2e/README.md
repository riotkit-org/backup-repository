E2E tests
=========

End-To-End tests are common for **Backup Repository server**, **Frontend application** and for **backup shell client "Bahub"**.

The scenarios are testing various user cases using multiple tools at once - for example creating access keys in frontend, then sending backup in shell client, and listing backups in frontend again.

Technology
----------

Tests are written using Behat 3.x framework. Scenarios are in **Cucumber** language, and the steps are implemented in PHP.


Running
-------

Daily development scenario, Backup Repository running on development server, Bahub used as module from local files.

```bash
rkd :e2e:install
rkd :e2e:browser:spawn-without-docker

# do not use on CI! When test fails wait for user input in console - pause the execution for debugging
export WAIT_BEFORE_FAILURE=true

# runs all scenarios
./vendor/bin/behat

# run one or multiple scenarios that begins with "As an administrator" description
./vendor/bin/behat --name="As an administrator"

# run tests marked with "@bahub" tag
./vendor/bin/behat --tags bahub
```

Running on CI
-------------

**CI assumptions are:**
- Application is running in PROD mode inside a docker container
- Bahub is running in a production-like container with configuration from `bahub/bahub.conf.yaml`
- There are test containers running like PostgreSQL, MySQL and other from docker-compose project placed there: `integration-env/docker-compose.bahub-dependencies.yml`
- Browser is dockerized with a VNC available optionally

```bash
# setup - one time
cd ../integration-env
rkd :run

# running tests
cd ../e2e
rkd :test --docker
```

Groups of suites
----------------

Test suites are grouped by application name and test type.

**Application tags:**
- `@bahub`
- `@server`

**Test types tags:**
- `@security`
- `@backup`
- `@realUseCase` - a complete user scenario, not focused to be per-module, but actually how the set of applications are used in real case scenario
- `@docker` - tests that are manipulating with docker containers ex. stopping, starting

To run `@bahub` tagged tests type `./vendor/bin/behat --tags bahub`
