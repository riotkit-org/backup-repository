Security
========

Access
------

File can be PUBLIC or PRIVATE, the **public** attribute of input data that is sent together with file means the file will
be or not be listed by listing endpoint (unless the token is not an administrative token). Also the private file could be accessed only using token, who uploaded the file.

**Password protection** could be used to protect from downloading the file content by not authorized person, and also it will
anonymize the file in public listing if the person who lists the files will not know the password.

========  ==========  =======================  ====================  ======================================
 Access combinations
-----------------------------------------------------------------------------------------------------------
 public    password     Accessible in search    Censored in search    Who can download?
========  ==========  =======================  ====================  ======================================
 Yes       No           Yes                     No                    Everybody
 Yes       Yes          Yes                     Yes                   Everybody, who knows password
 No        No           No                      N/A                   Person, who uploaded
 No        Yes          No                      N/A                   Person, who upload and knows password
========  ==========  =======================  ====================  ======================================

Uploading restrictions
----------------------

When you give user a temporary token to allow to upload eg. avatar, then you may require that the file will not have a **password**, and possibly enforce
to select some tags as mandatory.

======================================  =============================================================================================================
 Extra roles, that can restrict the      token
-----------------------------------------------------------------------------------------------------------------------------------------------------
 name                                    description
======================================  =============================================================================================================
upload.enforce_no_password               Enforce files uploaded with this token to not have a password
upload.enforce_tags_selected_in_token    Regardless of tags that user could choose, the tags from token will be copied into each uploaded file
======================================  =============================================================================================================
