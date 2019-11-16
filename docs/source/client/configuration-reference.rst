..  _bahub_configuration_reference:

Configuration reference
=======================

There are **3 sections**:

- Access: Describes authorization details, name it eg. server1 and put url and token
- Encryption: Encryption type and password (if any) to encrypt your files stored on **File Repository**
- Backups: Describes where is your data, how to access it and under which COLLECTION to send it to **File Repository**
- Recoveries: Recovery plans. A policy + list of "backups" to restore within a single command


Example scenario:

1. You have a server under https://backups.iwa-ait.org and token "XXX-YYY-ZZZ-123", you name it "ait_backups" under **access** section
2. You want to have encrypted backups using AES 256 CBC, then you add "ait_secret" under **encryption** with passphrase "something-secret" and type "aes-256-cbc"
3. Next you want to define where is the data, in our example it's in a docker container under /var/lib/mysql and we want to send this data to collection "123-456-789-000". You should reference "ait_backups" access and "ait_secret" as the encryption method for your backup there.


Environment variables
---------------------

If you want to use environment variables, use bash-like syntax *${SOME_ENV_NAME}*.

**NOTE: In case you will not set a variable in the shell, then application will not start, it will throw a configuration error.**

Application configuration
-------------------------

**Notice:** Below example uses environment variables eg. ${DB_HOST}, you may want to replace them with values like localhost or others

.. literalinclude:: ../../../bahub-client/configuration.yaml.dist
   :language: yaml
