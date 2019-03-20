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
