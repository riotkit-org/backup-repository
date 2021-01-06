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

# runs all scenarios
./vendor/bin/behat

# run one or multiple scenarios that begins with "As an administrator" description
./vendor/bin/behat --name="As an administrator"
```
