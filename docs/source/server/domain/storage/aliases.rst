Aliasing filenames (migrating existing files to File Repository)
================================================================

Filename in **File Repository** is created based on file contents hash + name submitted by user.
To allow **easier migration of your existing files**, the **File Repository** allows to create *aliases* to files you upload.


Scenario
--------

Let's assume that you have a file named "Accidential-Anarchist.mp4", and your website shows a player that points to https://static.iwa-ait.org/Accidential-Anarchist.mp4
Now you want to migrate your storage to use **File Repository**, so the **File Repository** will store and serve the files with help of your webserver.


To keep old links still working you need to:

    - Set up a URL rewrite in your webserver (eg. NGINX or Apache 2) to rewrite the FORMAT OF THE URL, example: /education/movies/watch?v=... to /repository/file/...
    -  You have a file "Accidential-Anarchist.mp4", after uploading to File Repository it will have different name eg. "59dce00bcAccidential-Anarchist.mp4", you can create an alias that will point from "Accidential-Anarchist.mp4" to "59dce00bcAccidential-Anarchist.mp4"


Practice, defining aliases
--------------------------

To start you need to create a file `config/ids_mapping.yaml`, where you will list all of the aliases in YAML syntax.

Example:

.. literalinclude:: ../../../../../server/config/ids_mapping.yaml.example
   :language: yaml


*Notice: You need to restart the application (or execute ./bin/console cache:clear --env=prod) after applying changes to this file*

