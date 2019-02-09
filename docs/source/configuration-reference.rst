Configuration reference
=======================

Application configuration
-------------------------

.. literalinclude:: ../../.env.dist

Docker environment + application configuration reference
--------------------------------------------------------

.. literalinclude:: ../../examples/docker/.env.dist

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
