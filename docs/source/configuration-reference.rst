Configuration reference
=======================

Application configuration
-------------------------

When setting up application without a docker a .env file needs to be created in the root directory of the application.
The .env.dist is a template with example, reference values. If you use a docker image, then you may use those variables as environment variables for the container.

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

=============================  =====================================================================================
-----------------------------  -------------------------------------------------------------------------------------
 Name and example               Description
=============================  =====================================================================================
  WAIT_FOR_HOST=db_mysql:3306   (optional) Waits up to 2 minutes for host to be up when starting a container
  FEATURES=gateway-real-ip      (optional) Feature toggle, see section "NGINX features"
  SENTRY_DSN=url-here           (optional) Enables integration with sentry.io, so all failures will be logged there
=============================  =====================================================================================


NGINX features
--------------

The NGINX configuration can be easily extend with toggleable features.

Example:

1. Place a feature here in "feature.d"
2. As a first line add a comment header `#@feature: /etc/nginx/features/fastcgi.d`
3. Enable the feature with environment variable eg. `FEATURES=gateway-real-ip,some-other-feature`


There are multiple sections in NGINX configuration file where you can attach a feature.

==================================  ====================================================================================
----------------------------------  ------------------------------------------------------------------------------------
 Path                                Description
==================================  ====================================================================================
 /etc/nginx/features/http.d/         Inside of a http { } block
 /etc/nginx/features/server.d/       Inside of a server { } block, but outside of a location { }
 /etc/nginx/features/fastcgi.d/      Inside of a location { } block, where the fastcgi is configured
==================================  ====================================================================================


Built-in NGINX features
-----------------------

==================================  ====================================================================================
----------------------------------  ------------------------------------------------------------------------------------
 Feature                                Description
==================================  ====================================================================================
 gateway-real-ip                     Populate REMOTE_ADDR and X-Real-Ip with values from parent NGINX (gateway/front)
==================================  ====================================================================================
