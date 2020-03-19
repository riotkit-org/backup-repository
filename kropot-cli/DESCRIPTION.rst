Kropot-CLI
==========

                                ██████████████
                        ████████▓▓▓▓██░░░░██▓▓████
                ████████░░░░░░░░██▓▓██░░░░██▓▓▓▓▓▓██
            ████░░██▓▓▓▓██░░░░░░██▓▓▓▓██░░██▓▓▓▓▓▓▓▓██
        ████░░░░░░░░██▓▓▓▓██░░░░██▓▓▓▓██░░██▓▓▓▓▓▓▓▓██
      ██▓▓▓▓██░░░░░░░░██▓▓▓▓██░░██▓▓▓▓██░░██▓▓▓▓▓▓██
    ██▓▓▓▓▓▓▓▓██░░░░░░██▓▓▓▓██░░██▓▓▓▓██░░██▓▓▓▓▓▓██
    ██▓▓▓▓▓▓▓▓▓▓██░░░░██▓▓▓▓██░░██▓▓▓▓▓▓██▓▓▓▓▓▓▓▓██
    ██▓▓▓▓▓▓▓▓▓▓▓▓██░░██▓▓▓▓▓▓██▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓██
    ██▓▓▓▓▓▓▓▓▓▓▓▓▓▓██▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓████
    ██▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓████
      ████▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓████
          ████████▓▓▓▓▓▓▓▓▓▓▓▓██████
                  ████████████

Tiny File Repository client supporting only storage mirroring for backup purposes.
It does not provide the replication functionality, just a secure copy - simple possibility to backup.
Kropot-CLI will better redistribute your bread, in a safest way.

**Features:**

- Simple storage mirroring (not replicating)
- Copies all the data just like rsync would do but with additional options
- Supports any storage type on File Repository server (including any network filesystems such as S3, it does not care about storage type)
- **Supports zero-knowledge data mirroring**, the Little Copy Machine client can store encrypted data without having knowledge about what it is (no any rsync or other tool guarantee this feature)
- Client->Server architecture, simple to setup. **The Little Copy Machine can be behind NAT/VPN, as a hidden service**
- **Scalability**. Run multiple instances, connect to same database to avoid collision (if storing on same storage location)
