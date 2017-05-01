Multiple applications in one database
=====================================

To configure `Wolnościowiec File Repository` to be in the same database next to
other application tables there is a possibility to prefix all tables.

The prefix could be defined in the environment configuration.

```
// prefix table names (optionally)
$dbOptions = $app['db.options'];
$dbOptions['prefix'] = 'wfr_';
$app['db.options'] = $dbOptions;
```

To override environment configuration settings just create a file in the `config` directory
with name of the environment suffixed with `.custom.php`

Examples:
- `config/prod.custom.php`
- `config/dev.custom.php`
- `config/test.custom.php`

Example of `prod.custom.php` file:

```
<?php

$app['api.key'] = 'my-secret-key-is-here-and-this-file-is-ignored-on-git-and-accesible-only-on-production';
$app['https.force'] = true;

$dbOptions = $app['db.options'];
$dbOptions['prefix'] = 'wfr_';
$app['db.options'] = $dbOptions;

return $app;
```

Note
----

Changing the prefix on existing database structure may result in not properly working rollback in migrations.

### Development side

Every new migration must extend the `BaseMigration` class and use `createTableName()` method to be compatible with enabled prefix.