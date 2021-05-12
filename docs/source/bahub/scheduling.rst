Scheduling backups
##################

Jobber
******

Jobber is a tiny, modern cron-like scheduler that is pre-installed by default in official Bahub docker container.
In official Bahub docker container the Jobber configuration file is placed at :class:`/home/bahub/.jobber`, it can be mounted using docker volumes to modify the contents of the file.

Configuring
-----------

Recommended configuration is to use **RKD Pipelining feature** to run multiple backups one-after-one, starting at given time.
Prepared example starts backups at 02:30 in the night, uses :class:`@` operator to append :class:`--keep-going` to all next defined backup tasks, in effect even if one backup fails a next one will be executed.

**Notice:** *Do not set same log file for both logPath and runLog.path to avoid error** :class:`size is not multiple of entry size`

.. code:: yaml

    version: 1.4

    prefs:
        logPath: /home/bahub/logs/jobber.general.log
        runLog:
            type: file
            path: /home/bahub/logs/jobber.run.log
            maxFileLen: 25m
            maxHistories: 2

    jobs:
        nightly:
            cmd: "bahub @ --keep-going :backup:make postgres_all_dbs  :backup:make git_fs  :backup:make nginx  :backup:make redis  :backup:make pastebin"
            # SEC/MIN/HR/MDAY/MTH/WDAY
            time: "00 30 02 * * *"
            onError: Continue
            notifyOnFailure:
                - type: system-email


Using jobber
------------

Listing defined jobs
^^^^^^^^^^^^^^^^^^^^

.. code:: bash

    $ jobber list
    NAME     STATUS  SEC/MIN/HR/MDAY/MTH/WDAY  NEXT RUN TIME              NOTIFY ON SUCCESS  NOTIFY ON ERR  NOTIFY ON FAIL  ERR HANDLER
    nightly  Good    0 30 2 * * *              May 01 02:30:00 +0000 UTC                                    system-email    Continue

Reloading configuration file
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code:: bash

    $ jobber reload
    Loaded 1 jobs.


Running manually a job right now
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code:: bash

    $ jobber test nightly
    # ... bahub will be started immediately ...


Viewing job definition
^^^^^^^^^^^^^^^^^^^^^^

.. code:: bash

    $ jobber cat nightly
    bahub @ --keep-going :backup:make postgres_all_dbs  :backup:make git_fs  :backup:make nginx  :backup:make redis  :backup:make pastebin


Checking job history
^^^^^^^^^^^^^^^^^^^^

.. code:: bash

    $ jobber log nightly
    TIME                  JOB      RESULT     NEW JOB STATUS
    May 12 06:30:00 2021  nightly  cancelled  Good
