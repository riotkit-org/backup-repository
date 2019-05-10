Configuration reference
=======================

Application configuration
-------------------------

.. literalinclude:: ../../.env.dist

..  _permissions_reference:

Permissions list
----------------

You can get a permissions list by accessing an endpoint in your application:

.. code:: json

    GET /auth/roles?_token=test-token-full-permissions

There is also an always up-to-date permissions list, taken directly from the recent version of the application.

How to read the list by example:

.. code:: php

    /** Allows to upload images */
    public const ROLE_UPLOAD_IMAGES            = 'upload.images';

Legend:

    - Between /\*\* and \*/ is the description
    - **upload.images** is the role name


.. literalinclude:: ../../src/Domain/Roles.php
   :language: ruby

Docker container extra parameters
---------------------------------

Parameters passed to docker container are mostly application configuration parameters, but not only.
There exists extra parameters that are implemented by the docker container itself, they are listed below:

=============================  ====================================================================================
-----------------------------  ------------------------------------------------------------------------------------
 Name and example               Description
=============================  ====================================================================================
  WAIT_FOR_HOST=db_mysql:3306   (optional) Waits up to 2 minutes for host to be up when starting a container
=============================  ====================================================================================

