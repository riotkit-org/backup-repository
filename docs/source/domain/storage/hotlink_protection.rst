Hotlink protection - personalizing URLs for your visitors
=========================================================

If for any reason you need to secure your content from being distributed outside of your website, then you need a hotlink protection.
Hotlink protection gives your website a **control over who can see the video, image or any other resource that is kept on File Repository**.


Preparing your website and File Repository configuration
--------------------------------------------------------

A website that is displaying eg. a video player that would play a video from **File Repository** need to point to a personalized URL address
especially generated for your page visitor.


At first let's look at the URL format, you need to define a URL format that will point to protected files.
Below there are multiple examples, you can configure the URL however you want, **this you need to adjust in your .env file or in environment variables in Docker container**.

.. code:: bash

    # example 1
    ANTI_HOTLINK_URL=/stream/{accessToken}/{expirationTime}/{fileId}

    # example 2
    ANTI_HOTLINK_URL=/video/{accessToken}/{expirationTime}/{fileId}

    # example 3
    ANTI_HOTLINK_URL=/watch/{fileId},{accessToken},{expirationTime}

    # example 4
    ANTI_HOTLINK_URL=/watch/{accessToken}/{fileId}


So, let's take a look at the most interesting part - the access token generation.


**Each visitor on your page needs to get a unique access token** that will allow to see the file content **only for him/her**.
To generate such access token we need to **DEFINE A COMMON FORMAT that your application will use and File Repository will understand**.

.. code:: bash

    ANTI_HOTLINK_SECRET_METHOD="\$http_x_expiration_time\$http_x_real_uri\$http_x_remote_addr MY-AWESOME-SUFFIX"


Following example is combining most important variables, why?

- $http_x_real_uri - to restrict this token only to single file (this header may be required to be set on NGINX/Apache level)
- $http_x_remote_addr - to restrict access to single IP address
- MY-AWESOME-SUFFIX - this one definitely you should change to a SECRET you only know. It will prevent anybody from generating a token
- $http_x_expiration_time - optionally validate the passed input data in the url


Generally the rule with the variables is simple as in NGINX, but a little bit more extended to give better possibilities.

========================  =============================================================================================
Variable templates
-----------------------------------------------------------------------------------------------------------------------
 name                      description
========================  =============================================================================================
$http_xxx                   In place of xxx put your normalized header name eg. Content-Type would be content_type
$server_xxx                 Everything what is in PHP's $_SERVER, including environment variables
$query_xxx                  Everything what is in query string (query string in URL is everything after question mark)
========================  =============================================================================================


Practical example of generating access token on your website
------------------------------------------------------------

Assuming that you have following configuration:

.. code:: bash

    ANTI_HOTLINK_PROTECTION_ENABLED=true
    ANTI_HOTLINK_RESTRICT_REGULAR_URLS=false
    ANTI_HOTLINK_CRYPTO=md5
    ANTI_HOTLINK_SECRET_METHOD="\$http_x_expiration_time\$filename\$http_remote_addr MY-AWESOME-SUFFIX"
    ANTI_HOTLINK_URL=/stream/{accessToken}/{expirationTime}/{fileId}


That would be an example code that could generate URL addresses in your application:

.. code:: php

    <?php
    $fileId = 'Accidential-Anarchist.mp4';
    $expirationTime = time() + (3600 * 4); // +4 hours
    $rawToken = $expirationTime . $fileId . ($_SERVER['REMOTE_ADDR'] ?? '') . ' MY-AWESOME-SUFFIX';

    $hash = hash('md5', $rawToken);
    echo 'URL: /stream/' . $hash . '/' . $expirationTime . '/' . $fileId;

