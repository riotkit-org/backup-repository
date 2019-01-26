Files storage
=============

The file storage is like a bag of files, there are no directories, it's more like an object storage.
When you put some file it is written down on the disk, and it's metadata is stored in the database.


Files could be tagged with some names, it's useful if the repository is shared between multiple usage types.
The listing endpoint can search by tag, phrase, mime type - the external application could use listing endpoint
to show a gallery of pictures for example, uploaded documents, attachments lists.


In short words the **File Storage** is a specialized group of functionality that allows to manage files, group them, upload new, delete and list them.


.. toctree::
   :maxdepth: 2
   :caption: Contents:

   security
   uploading
   downloading
   aliases
   hotlink_protection

