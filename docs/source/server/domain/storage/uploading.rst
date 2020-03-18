Uploading
=========

Files could be uploaded in three ways - as RAW BODY, as POST form field and as URL from existing resource in the internet.

========================  =============================================================================================
Common parameters for       all endpoints
-----------------------------------------------------------------------------------------------------------------------
 name                      description
========================  =============================================================================================
tags                       List of tags where the file will be listed
public                     Should be listed/searched? (true/false)
password                   Optionally allows to protect access to the file and it's metadata
encoding                   Allows to upload encoded file, example values: base64, '' (helpful for frontend implementation)
========================  =============================================================================================



From external resource by URL
-----------------------------

========================  =============================================================================================
   Endpoint specific parameters
-----------------------------------------------------------------------------------------------------------------------
 name                      description
========================  =============================================================================================
fileUrl                    URL address to the file from the internet
========================  =============================================================================================

.. code:: json

    POST /repository/image/add-by-url?_token=some-token-there

    {
        "fileUrl": "http://zsp.net.pl/files/barroness_logo.png",
        "tags": [],
        "public": true
    }

In RAW BODY
-----------

========================  =============================================================================================
   Endpoint specific parameters
-----------------------------------------------------------------------------------------------------------------------
 name                      description
========================  =============================================================================================
filename                   Filename that will be used to access the file later
========================  =============================================================================================

.. code:: json

    POST /repository/file/upload?_token=some-token-here&fileName=heart.png

    < some file content there instead of this text >


Notes:

    - Filename will have added automatically the content hash code to make the record associated with file content (eg. heart.png -> 5Dgds3dqheart.png)
    - Filename is unique, same as file
    - If file already exists under other name, then it's name will be returned (deduplication mechanism)

In a POST form field
--------------------

========================  =============================================================================================
   Endpoint specific parameters
-----------------------------------------------------------------------------------------------------------------------
 name                      description
========================  =============================================================================================
filename                   Filename that will be used to access the file later
========================  =============================================================================================


.. code:: json

    POST /repository/file/upload?_token=some-token-here&fileName=heart.png

    Content-Type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW

    ------WebKitFormBoundary7MA4YWxkTrZu0gW
    Content-Disposition: form-data; name="file"; filename=""
    Content-Type: image/png


    ------WebKitFormBoundary7MA4YWxkTrZu0gW--

    ... file content some where ...
