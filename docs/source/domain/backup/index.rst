Backup
======

Backup *collections* allows to store multiple *versions* of the same file.

Each submitted *version* has **automatically incremented version number by one**.

Example scenario with strategy "delete_oldest_when_adding_new":

.. code:: javascript

    Given we have DATABASE dumps of iwa-ait.org website
    And our backup collection can contain only 3 versions (maximum)

    When we upload a sql dump file THEN IT'S a v1 version
    When we upload a next sql dump file THEN IT'S a v2 version
    When we upload a next sql dump file THEN IT'S a v3 version

    Then we have v1, v2, v3

    When we upload a sql dump file THEN IT'S a v4 version
    But v1 gets deleted because collection is full

    Then we have v2, v3, v4


From security point of view there is a possibility to attach multiple tokens with different access rights to view and/or manage the collection.


.. toctree::
   :maxdepth: 2
   :caption: Contents:

   getting-started
   collections
   authorization
   versioning
   replication
