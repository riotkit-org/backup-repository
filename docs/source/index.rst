.. File Repository documentation master file, created by
   sphinx-quickstart on Thu Jan  3 18:33:15 2019.

File Repository's documentation
===============================

File Repository is a modern API application dedicated for storing files.
It is able to use various storage backends including AWS S3, Dropbox, Google Drive and just filesystem.
Lightweight, requires just PHP7 and at least SQLite3 or MySQL (other databases can be also supported in future due to using ORM).

Main functionality:

- Strict access control, you can **generate a token** that will have access to specific actions on specific items
- Store files where you need; on **AWS S3, Minio.io, Dropbox, Google Drive, FTP, SFTP, and others...**
- **Deduplication for non-grouped files**. There will be no duplicated files stored on your disk
- **Backups management**, you can define a collection of file versions that can **rotate on adding a new version**
- Pure API, you can choose any frontend, use it internally in your application, or create your own frontend

.. toctree::
   :maxdepth: 2
   :caption: Contents:

   domain/backup/index
