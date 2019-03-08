Bahub API client
================

Bahub is an automation tool for uploading and restoring backups.
Works in shell, can work as a docker container in the same network with scheduled automatic backups of other containers, or can work
as an UNIX daemon on the server without containerization.

.. image:: ../_static/screenshots/bahub/bahub-1.png

.. toctree::
   :maxdepth: 2
   :caption: Contents:

   configuration-reference
   basic-usage

Setup
-----

Bahub can be running as a separate container attached to docker containers network or manually as a regular process.
The recommended way is to use a docker container, which provides a working job scheduling, installed dependencies and preconfigured most of the things.

Using docker container
----------------------

There exists a bahub tag on the docker hub container, **wolnosciowiec/file-repository:bahub**
You can find an example in "examples/client" directory in the repository_.

**docker-compose.yml**

.. literalinclude:: ../../../examples/client/docker-compose.yml
   :language: yaml

**/cron**

.. literalinclude:: ../../../examples/client/cron
   :language: bash

**/bahub.conf.yaml** (see: :ref:`bahub_configuration_reference`)

.. literalinclude:: ../../../examples/client/config.yaml
   :language: bash

*Note: It's very important to specify the project name in docker-compose with "-p", so it will have same value as "COMPOSE_PROJECT_NAME". You may want to add it to .env file and reuse in Makefile and in docker-compose.yml for automation**

.. _repository: https://github.com/riotkit-org/file-repository/tree/master/examples

Using bare metal
----------------

Use Python's PIP to install the package, and run it.

.. code:: shell

    pip install bahub
    bahub --help

