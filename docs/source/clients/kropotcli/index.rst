KropotCLI
=========

Storage client that provides a bulletproof backup of whole File Repository storage.
It is not just a rsync-like tool, neither HA or a replication.
**KropotCLI is a continuous synchronization tool with extra layer of optional encryption, that provides a zero-knowledge copy of your data in any location.**


**Requirements:**

- Python 3.6+ (with dependencies listed in requirements.txt)
- Optionally docker (if using a docker container)
- SQLite3, PostgreSQL or MySQL database (any supported by SQLAlchemy, defaults to SQLite3, actually SQLite3 is fully tested and recommended)

*KropotCLI motto is to better redistribute the bread*


Mechanism and security
----------------------

KropotCLI uses server's :ref:`secure_copy` domain. You can read about the conception at the :ref:`secure_copy` page.

The security of the stored passphrases in the tokens are described in :ref:`token_secrets`

How it works from KropotCLI perspective?
----------------------------------------

1. The server grants restricted access to the storage with SecureCopy credentials which includes passphrase, encryption algorithm, salt, digest method, rounds etc.
2. **The passphrase and digest salt assigned for token is not know to the token user**
3. The server is encrypting the data on-the-fly for KropotCLI
4. **The KropotCLI is storing the data without any knowledge about their content and even filenames!** That's called **zero-knowledge** storage.

Getting started
---------------

1. **Generate a synchronization token**

Use our example request, fill up the values.

.. code:: json

    {
      "id": ".................",
      "roles": [
        "securecopy.stream"
      ],
      "expires": "2030-05-01 01:06:01",
      "data": {
        "tags": [],
        "allowedMimeTypes": [],
        "maxAllowedFileSize": "0",
        "allowedIpAddresses": [],
        "allowedUserAgents": [],
        "secureCopyEncryptionKey": "........................",
        "secureCopyEncryptionMethod": "aes-256-cbc",
        "secureCopyDigestMethod": "sha512",
        "secureCopyDigestRounds": 6000,
        "secureCopyDigestSalt": ".............................."
      }
    }

The request needs to be submitted at :ref:`creating_a_token`

2. **Install KropotCLI**

a) Use docker image from **quay.io/riotkit/kropot-cli:VERSION**

The image takes cli arguments in command, so use *docker run --rm quay.io/riotkit/kropot-cli --help* for usage.

*See all available versions of docker images there: https://quay.io/repository/riotkit/kropot-cli?tab=tags*

b) Use **pip install kropotcli** to install as Python package.

3. **Run the synchronization**

.. code:: bash

    kropotcli --token=.................... \
        --storage-path=/mnt/storage \
        --server-url=https://api.storage.iwa-ait.org \
        --instance-name=iwa-storage-replica-1-1 \
        --log-level=debug \
        collect
