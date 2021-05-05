Configuration reference
=======================

1. API documentation
--------------------

API documentation is accessible at application's endpoint, take a look at http://localhost/api/stable/doc

2. Storage
------------------------

1) :class:`FS_RW_NAME` and :class:`FS_RO_NAME` defines NAMES of configuration, for example :class:`FS_LOCAL_DIRECTORY` - here the "LOCAL" is the configuration name.
2) **For most of the cases it is enough to have same adapter in both RO and RW slots.**
3) Following default configuration is using local Min.io storage available on http://localhost:9000, you can run Min.io in docker
4) If you don't have any cloud storage, and don't want to use Min.io, just switch :class:`FS_RW_NAME` and :class:`FS_RO_NAME` to :class:`"LOCAL"`. **If you are using docker, then remember about mounting the path FS_LOCAL_DIRECTORY, else all files will disappear after container restart/recreation.**

.. literalinclude:: ../../../server/.env.dist
   :start-after: <docs:storage>
   :end-before: </docs:storage>

3. Hard limits
--------------

Global, hard limits can be configured for whole Backup Repository instance.
Those would take effect also for administrators.

.. literalinclude:: ../../../server/.env.dist
   :start-after: <docs:backups>
   :end-before: </docs:backups>

4. Security
-----------

**JWT - JSON Web Tokens** are used to grant access to system for multiple users, defining the level of access for various resources.
To generate JWT there are server-side keys used. Keys needs to be generated before launching the application first time, and **must be kept IN SECRET!**
The passphrase should be long and unique, so nobody could guess it. Use a password generator to generate a strong password. Avoid using "$", blank spaces and various quotes as characters.

.. literalinclude:: ../../../server/.env.dist
   :start-after: lexik/jwt-authentication-bundle
   :end-before: < lexik/jwt-authentication-bundle


**Generating JWT keys**

Please replace $JWT_PASSPHRASE with your actual passphrase.

.. code:: bash

    openssl genpkey -out config/jwt/private.pem -aes256 -pass pass:$JWT_PASSPHRASE -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:$JWT_PASSPHRASE
