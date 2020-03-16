Secure Copy and token secrets
=============================

Fields such as **data.secureCopyEncryptionKey** and **data.secureCopyDigestSalt** are kept in the token in encrypted form, so no any user could look up those values.

The values are encrypted using system-wide credentials in File Repository, those credentials are defined in configuration.
