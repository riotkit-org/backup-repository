Little Copy Machine
===================

Tiny File Repository client supporting only storage mirroring for backup purposes.
It does not provide the replication functionality, just a secure copy - simple possibility to backup.

**Features:**
- Simple storage mirroring (not replicating)
- Copies all the data just like rsync would do but with additional options
- Supports any storage type on File Repository server (including any network filesystems such as S3, it does not care about storage type)
- **Supports zero-knowledge data mirroring**, the Little Copy Machine client can store encrypted data without having knowledge about what it is (no any rsync or other tool guarantee this feature)
- Client->Server architecture, simple to setup. **The Little Copy Machine can be behind NAT/VPN, as a hidden service**
- **Scalability**. Run multiple instances, connect to same database to avoid collision (if storing on same storage location)

**Future plans:**
[ ] Recover single file, or multiple files by sending them to File Repository instance using automated Little Copy Machine command

Installing
----------

- Via PIP

```bash
pip install littlecopymachine
```

- Via setuptools from repository

```bash
git clone https://github.com/riotkit-org/file-repository.git
cd file-repository

git checkout VERSION-YOU-WANT # else you will get a development version

cd little-copy-machine-client
python3 setup.py install
```

- Via Makefile from repository

```bash
git clone https://github.com/riotkit-org/file-repository.git
cd file-repository

git checkout VERSION-YOU-WANT # else you will get a development version

cd little-copy-machine-client
make install
```

Development
-----------

Use `make` command to navigate through all possible automated commands helpful in development.
To run the application manually from the repository use `cd src && python -m riotkit.filerepository.littlecopymachine`

Copyleft
--------

Created by **RiotKit Collective**.
Project created originally to be able to redistribute data to many places in a ZERO-KNOWLEDGE manner (only primary server is able to encrypt/decrypt the data).
