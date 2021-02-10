E2E tests
=========

End-To-End tests are common for **Backup Repository server**, **Frontend application** and for **backup shell client "Bahub"**.

The scenarios are testing various user cases using multiple tools at once - for example creating access keys in frontend, then sending backup in shell client, and listing backups in frontend again.

Technology
----------

Tests are written using Behat 3.x framework. Scenarios are in **Cucumber** language, and the steps are implemented in PHP.


Running
-------

```bash
composer install

# do not use on CI! When test fails wait for user input in console - pause the execution for debugging
export WAIT_BEFORE_FAILURE=true

# runs all scenarios
./vendor/bin/behat

# run one or multiple scenarios that begins with "As an administrator" description
./vendor/bin/behat --name="As an administrator"

# run tests marked with "@bahub" tag
./vendor/bin/behat --tags bahub
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

To run `@bahub` tagged tests type `./vendor/bin/behat --tags bahub`
