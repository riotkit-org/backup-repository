Technical shell commands
========================

For better experience and less frustration we implemented a few commands that improves deployment and overall application maintenance.
Also the Symfony Framework, that we use implements tons of useful shell utilities worth looking at.

Wait for database
-----------------

.. code:: bash

    # wait for application's database to be ready
    ./bin/console health:wait-for:database --timeout=300


Wait for other File Repository instance to be up
------------------------------------------------

.. code:: bash

    ./bin/console health:wait-for:instance https://api.backups.riotkit.org --timeout=2 --token=9b46f515-86d3-4d81-84e9-d4f5434f98f7

Health check
------------

Does same thing as HTTP health check endpoint, but does not require authorization.

.. code:: bash

    ./bin/console health:check

Dump database connection information
------------------------------------

Very useful in debugging configuration of the database. The command dumps already parsed parameters of the Doctrine DBAL driver.

.. code:: bash

    $ ./bin/console doctrine:connection:info
    ==> Parameters:
    ^ array:12 [
      "driver" => "pdo_pgsql"
      "charset" => "UTF8"
      "default_dbname" => "rojava"
      "dbname" => "rojava"
      "host" => "/var/run/postgresql"
      "password" => "rojava"
      "user" => "riotkit"
      "port" => "5432"
      "path" => "./var/data.db"
      "driverOptions" => []
      "serverVersion" => "11"
      "defaultTableOptions" => array:2 [
        "charset" => "UTF8"
        "collate" => "pl_PL.UTF8"
      ]
    ]

    ==> Database:
    ^ "rojava"

    ==> Platform:
    ^ "postgresql"

