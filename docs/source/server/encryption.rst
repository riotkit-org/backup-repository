.. _cryptography_spec:

Specification of encryption
===========================

Key
---

Key is a hash from passphrase using PBKDF2.

- Salt: IV is used as salt
- Key size: 16
- Rounds: number of rounds specified in ENC_DIGEST_ROUNDS for system-wide usage, in token "data.secureCopyDigestRounds" when using SecureCopy
- Digest: algorithm specified in ENC_DIGEST_ALGORITHM for system-wide usage, in token "data.secureCopyDigestMethod" when using SecureCopy


**Generating a PBKDF2 key using PHP built-in method:**

.. code:: php

    $key = openssl_pbkdf2(
        'my-password-here', // passphrase
        'iv-there',         // initialization vector
        16,                 // key size
        6000,               // rounds
        'sha512'            // digest algorithm
    );

*TIP: You can use psysh to execute this code*

Example of decryption using OpenSSL shell utility
-------------------------------------------------

.. code:: bash

    openssl enc -d -aes-256-cbc -pbkdf2 -K {{ PBKDF key here as hex }} -iv {{ IV as hex there }}
