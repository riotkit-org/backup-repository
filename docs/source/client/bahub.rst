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
   monitoring

Setup
-----

Bahub can be running as a separate container attached to docker containers network or manually as a regular process.
The recommended way is to use a docker container, which provides a working job scheduling, installed dependencies and a warranty that it will work.

Using docker container
----------------------

There exists a repository **quay.io/riotkit/bahub**, for testing purposes you can pick "latest-build" tag, for production it is recommended to pick a fixed version.
Example installation on the simplest construction - docker-compose is placed in "examples/client" directory in the repository_.

**Running a demo**

We prepared a demo environment that has a Bahub client, a Redis container and the File Repository server in one network.
It's recommended to have client and server separated on different servers.

Take a look around, and create your own docker-compose, kubernetes or plain docker setup basing on our demo configuration.

.. code:: bash

    git clone https://github.com/riotkit-org/file-repository
    cd file-repository/examples/client
    make start
    make sh

.. _repository: https://github.com/riotkit-org/file-repository/tree/master/examples/client

Without docker
--------------

Use Python's PIP to install the package, and run it.

.. code:: shell

    pip install bahub
    bahub --help

