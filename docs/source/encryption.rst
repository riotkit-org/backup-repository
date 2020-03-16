Specification of encryption
===========================

Key
---

Key is a hash from passphrase using PBKDF2.

- Salt: IV is used as salt
- Key size: 16
- Rounds: number of rounds specified in ENC_DIGEST_ROUNDS for system-wide usage, in token "data.secureCopyDigestRounds" when using SecureCopy
- Digest: algorithm specified in ENC_DIGEST_ALGORITHM for system-wide usage, in token "data.secureCopyDigestMethod" when using SecureCopy

.. code:: php

    $key = openssl_pbkdf2(
        $spec->getPassphrase()->getValue(),
        $iv,
        $spec->getCryptoAlgorithm()->getKeySize(),
        $spec->getDigestAlgorithm()->getRounds(),
        $spec->getDigestAlgorithm()->getName()
    );

Example of decryption using OpenSSL shell utility
-------------------------------------------------

.. code:: bash

    openssl enc -d -aes-256-cbc -pbkdf2 -iter 6000 -salt -md sha512 -K {{ PBKDF key here as hex }} -v {{ IV as hex there }}
