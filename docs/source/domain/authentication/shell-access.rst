Managing authentication using console commands
==============================================

Tokens can be easily generated without touching the cURL or browser or any API client.
Just use the console.

Generating an unlimited administrative token
--------------------------------------------

Probably first time when you set up the **File Repository** you may want to create a token, that will allow you to fully
manage everything. We already knew about such case and we're prepared for it! ;-)

.. code:: bash

    ✗ ./bin/console auth:generate-admin-token
    Generating admin token...
    ========================
    Form:
     [Role] -> security.administrator

    Response:
    ========================
    {
        "tokenId": "1B3B15EC-18E9-45DD-846B-42C5006E872A",
        "expires": "2029-02-11 07:24:42"
    }

In this case "1B3B15EC-18E9-45DD-846B-42C5006E872A" is your administrative token, pssst... keep it safe!

*Note: You can also generate a token with custom tokenId using the --id switch*

*Note: Use --ignore-error-if-token-exists in scripts*

Generating a normal token
-------------------------

It is considered a very good practice to minimize access to the resources. For example the server which will be storing
backups on the **File Repository** should only be allowed to send backups, not deleting for example.

For such cases you can generate a token that will allow access to specified collections and limit actions on them.

.. code:: bash

    ✗ ./bin/console auth:create-token --help
    Description:
      Creates an authentication token

    Usage:
      auth:create-token [options]

    Options:
          --roles=ROLES
          --tags=TAGS
          --mimes=MIMES
          --max-file-size=MAX-FILE-SIZE
      -i, --id[=ID]
          --expires=EXPIRES               Example: 2020-05-01 or +10 years
          --ignore-error-if-token-exists  Exit with success if token already exists. Does not check strictly permissions and other attributes, just the id.
      -h, --help                          Display this help message
      -q, --quiet                         Do not output any message
      -V, --version                       Display this application version
          --ansi                          Force ANSI output
          --no-ansi                       Disable ANSI output
      -n, --no-interaction                Do not ask any interactive question
      -e, --env=ENV                       The Environment name. [default: "test"]
          --no-debug                      Switches off debug mode.
      -v|vv|vvv, --verbose                Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

    Help:
      Allows to generate a token you can use later to authenticate in application for a specific thing


Example of generating a token with specified roles:

.. code:: bash

    ✗ ./bin/console auth:create-token --roles upload.images,upload.enforce_no_password --expires="+30 minutes" --id="A757A8CB-964F-4F7B-BB70-9DB2CF524BB9"
    ========================
    Form:
     [Role] -> upload.images
     [Role] -> upload.enforce_no_password

    Response:
    ========================
    {
        "tokenId": "A757A8CB-964F-4F7B-BB70-9DB2CF524BB9",
        "expires": "2019-02-11 08:01:00"
    }

*Note: When you not specify the --id, then the id will be generated automatically*

*Note: Use --ignore-error-if-token-exists in scripts*

Deleting expired tokens
-----------------------

This should be a scheduled periodic job in a cronjob, that would delete tokens that already are expired.

.. code:: bash

    ✗ ./bin/console auth:clear-expired-tokens
    [2019-02-05 08:07:01] Removing token 276CCE10-00C5-4CB6-9F9A-87934101BACE
