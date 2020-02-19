SecureCopy
==========

Storage mirroring with additional layer of security - encryption on the server side.

Features:

- Each client have it's own permissions.
- Encryption credentials are per-client (in token generated in File Repository)
- Black-box/Zero-knowledge encryption, the client only retrieve data
- External, ready to use client application "kropot-cli" that will help you to better redistribute your bread ;-)

=======================================================================  ===================================================================================
 The difference between "rsync" type tools and File Repository's Secure Copy
------------------------------------------------------------------------------------------------------------------------------------------------------------
 File Repository                                                          Other tools such as rsync
=======================================================================  ===================================================================================
 Token based authorization                                                 Requires SSH access
 Independent of storage filesystem (s3, local, other networked)            Requires local disk access
 Encryption on server side, without sharing the key to client              Impossible to perform a on-fly encryption, when client requests the files
 Requires additional setup time, requires database, maintenance time       Less maintenance, no database required, less frequent updates
 Brand new tool, nobody recognizes it                                      Everybody know the basic UNIX tools, less entry threshold
=======================================================================  ===================================================================================


.. image:: _static/diagrams/securecopy/architecture.png

.. toctree::
   :maxdepth: 2
   :caption: Contents:

   endpoints
