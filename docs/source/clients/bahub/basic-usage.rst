Basic usage
===========

Bahub is offering basic operations required to automate backup sending and receiving, not managing the server.

Sending a backup
----------------

.. code:: bash

    $ bahub --config ~/.bahub.yaml backup some_local_dir
    {'version': 72, 'file_id': 'E9D7103D-1789-475E-A3EE-9CF18F51ACA4', 'file_name': '2b2e269541backup.tar-v72.gz'}

Listing stored backups
----------------------

.. code:: json

    $ bahub --config ~/.bahub.yaml list some_local_dir
    {
        "v71": {
            "created": "2019-02-10 14:27:52.000000",
            "id": "1684C60D-28B0-4818-A3EC-1F0C47981592"
        },
        "v72": {
            "created": "2019-02-11 07:54:52.000000",
            "id": "E9D7103D-1789-475E-A3EE-9CF18F51ACA4"
        }
    }

Restoring a backup
------------------

Restoring latest version:

.. code:: json

    $ bahub --config ~/.bahub.yaml restore some_local_dir latest
    {"status": "OK"}

Restoring version by number:

.. code:: json

    $ bahub --config ~/.bahub.yaml restore some_local_dir v71
    {"status": "OK"}

Restoring version by id:

.. code:: json

    $ bahub --config ~/.bahub.yaml restore some_local_dir 1684C60D-28B0-4818-A3EC-1F0C47981592
    {"status": "OK"}

Recovery from disaster
----------------------

In case you need to quickly recover whole server/environment from backup - there is a **RECOVERY PLAN**.
A recovery plan is:

- List of backups to restore (names from section "backups")
- Policy of recovery (eg. recover everything, or stop on failure)

.. code:: yaml

    #
    # Recovery plans
    #   Restores multiple backups in order, using single command
    #
    #   Possible values:
    #     policy:
    #       - restore-whats-possible: Ignore things that cannot be restored, restore what is posible
    #       - stop-on-first-error: Restore until first error, then stay as it is
    #
    recoveries:
        default:
            policy: restore-whats-possible
            definitions: all

        plan_2:
            policy: stop-on-first-error
            definitions:
                - local_command_output

.. code:: json

    $ bahub --config ~/.bahub.yaml recover default

Making a snapshot of multiple services at once
----------------------------------------------

Snapshot works exactly in the same way as **recovery from diaster**, but it's inverted.
Instead of downloading a copy, it is actually uploading.

**NOTICE: Be very careful, as this is a single command to backup everything, remember about the backups rotation**

.. code:: json

    $ bahub --config ~/.bahub.yaml snapshot default

    [2019-04-01 07:17:42,818][bahub][INFO]: Performing snapshot
    [2019-04-01 07:17:42,819][bahub][INFO]: Performing a snapshot using "default" plan
    [2019-04-01 07:17:42,819][bahub][DEBUG]: shell(sudo docker ps | grep "test_1")
    [2019-04-01 07:17:42,870][bahub][DEBUG]: shell(set -o pipefail; sudo docker exec  test_1 /bin/sh -c "[ -e /etc ] || echo does-not-exist"; exit $?)
    [2019-04-01 07:17:42,967][bahub][DEBUG]: shell(set -o pipefail; sudo docker exec  test_1 /bin/sh -c "tar -czf - \"/etc\" "| openssl enc -aes-128-cbc -pass pass:Q*********************************************W; exit $?)
    [2019-04-01 07:17:43,052][bahub][DEBUG]: shell(set -o pipefail; sudo docker exec  test_1 /bin/sh -c "tar -czf - \"/etc\" "| openssl enc -aes-128-cbc -pass pass:Q*********************************************W; exit $?)
    [2019-04-01 07:17:45,672][bahub][DEBUG]: Request: https://api.backups.riotkit.org/repository/collection/d*************************************9/backup?_token=a***********************************6
    [2019-04-01 07:17:45,672][bahub][DEBUG]: response({"status":"OK","error_code":null,"exit_code":200,"field":null,"errors":null,"version":{"id":"***************","version":1,"creation_date":{"date":"2019-04-01 05:17:45.492490","timezone_type":3,"timezone":"UTC"},"file":{"id":110,"filename":"cd06f449fdtest-v2"}},"collection":{"id":"d*************************************9","max_backups_count":1,"max_one_backup_version_size":2000000000,"max_collection_size":8000000000,"created_at":{"date":"2019-03-24 21:29:14.000000","timezone_type":3,"timezone":"UTC"},"strategy":"delete_oldest_when_adding_new","description":"TEST","filename":"test"}})
    [2019-04-01 07:17:45,673][bahub][INFO]: Finishing the process

    {
        "failure": [],
        "success": [
            "test"
        ]
    }
