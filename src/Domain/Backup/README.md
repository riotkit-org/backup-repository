Backup domain
=============

BackupCollection
----------------

There are **collections** that are representing a single file in **multiple versions**.
A collection can define maximum versions count, maximum size of collection and maximum version size.

StoredVersion and StoredFile
----------------------------

Incrementing in-time version of the file that collection is representing.
Each StoredVersion is connected to a single StoredFile.
StoredFile represents a place where the file is stored in the storage.

Webhook
-------

Provides a possibility to send notification to external service.
Available events:
- Upload success
- Upload failure (out of space, upload error, etc.)
