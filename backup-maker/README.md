Backup Maker
============

Tiny backup client.

**Features:**
- Captures output from user-defined Backup/Restore commands
- Automated GPG support enables easy E2E encryption
- Buffered upload of backup made on-the-fly requires no additional disk space to create backup

Usage
-----

```bash
backupmaker make \
    --auth-token=... \
    --target-collection-id=31c0b32f-e1a0-40b9-ac3d-79c9d88d1dbe \
    --gpg-file=/mnt/public.gpg \
    --command="mysqldump ..."
```
