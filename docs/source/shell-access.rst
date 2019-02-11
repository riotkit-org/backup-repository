Shell access
============

File Repository usage can be automated using shell commands.
There are not so many commands, but basic usage could be automated using scripts.

Introduction
------------

Application is using *Symfony Console*, which is accessible in the main directory under **./bin/console**
In our prepared docker compose environment you may use it differently.

=================== ==================================================================================================================================================
   Usage examples depending on how application is set up
----------------------------------------------------------------------------------------------------------------------------------------------------------------------
 type                example
=================== ==================================================================================================================================================
 our docker env.     make console OPTS="backup:create-collection -d \"Some test collection\" -f "backup.tar.gz" -b 4 -o 3GB -c 15GB"
 docker standalone   sudo docker exec -it some_container_name ./bin/console backup:create-collection -d "Some test collection" -f "backup.tar.gz" -b 4 -o 3GB -c 15GB
 standalone/manual   ./bin/console backup:create-collection -d "Some test collection" -f "backup.tar.gz" -b 4 -o 3GB -c 15GB
=================== ==================================================================================================================================================

If something is not working as expected, there is an error and you would like to inspect it, then please add a "-vvv" switch to increase verbosity.

.. toctree::
   :maxdepth: 2
   :caption: Functionalities:

   domain/backup/shell-access
   domain/authentication/shell-access
