Listing and searching
=====================

Each file can be found by using a search endpoint.
Password protected files are censored, if the correct password was not entered in the search field.

*Note: Files can be named and tagged, marked as public/private, password protected.*

========================  =============================================================================================
Parameters
-----------------------------------------------------------------------------------------------------------------------
 name                      description
========================  =============================================================================================
 page                       Page number
 limit                      Limit results on single page
 password                   Password for password-protected files
 searchQuery                Search phrase, a word, multiple words to be searched for in the file name
 tags                       List of tags to filter by (array)
 mimes                      List of mimes to filter by (array)
========================  =============================================================================================

Example request:

.. code:: json

    GET /repository?_token=your-auth-token&page=1&limit=20


