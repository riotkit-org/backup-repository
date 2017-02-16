Wolnościowiec File Repository
=============================

[![Build Status](https://travis-ci.org/Wolnosciowiec/image-repository.svg?branch=master)](https://travis-ci.org/Wolnosciowiec/image-repository)
[![Code Climate](https://codeclimate.com/github/Wolnosciowiec/image-repository/badges/gpa.svg)](https://codeclimate.com/github/Wolnosciowiec/image-repository)
[![Test Coverage](https://codeclimate.com/github/Wolnosciowiec/image-repository/badges/coverage.svg)](https://codeclimate.com/github/Wolnosciowiec/image-repository/coverage)
[![Issue Count](https://codeclimate.com/github/Wolnosciowiec/image-repository/badges/issue_count.svg)](https://codeclimate.com/github/Wolnosciowiec/image-repository)
[![Heroku](https://heroku-badge.herokuapp.com/?app=image-repository-test&root=?_token=api-key-here-for-external-remote-control)](https://image-repository-test.herokuapp.com/?_token=api-key-here-for-external-remote-control)

Handles files as a node and exposes uploaded files
to end users. Could be used as a cheap cluster of storage
for files, or just as a simple static files storage.

Initially the project was created to store static files
to separate the traffic and conventionally the logic of the application
from static content, and the second target is to provide a possibility
to store the content on a very cheap $1/mo servers.

Free software
=============

Created for an anarchist portal, with aim to propagate the freedom and grass-roots social movements
where the human and it's needs is on first place, not the capital and profit.

![Anarchist syndicalism](docs/anarchosyndicalism.png)

Free to everyone, with restrictions for commercial usage.
Built using recent technologies. RESTful, scalable, automated with tests, lightweight and cheap to host.

![Silex](docs/silex-logo.png) ![Doctrine 2](docs/doctrine2-logo.png) ![Twig](docs/twig-logo.png)

Requirements for web hosting
============================

- PHP7

Requirements for the build
==========================

- Linux machine
- Composer
- ncftp

Installation on remote server (with FTP access only)
====================================================

Let's assume a harder situation, we don't have an access to the shell, just the FTP on shared hosting.
So, we will install the dependencies locally and put files on remote server using a deployment script.

```
# let's install the dependencies first, put application version
composer install --no-dev

# create custom configuration file
nano ./config/prod.custom.php
```

```php
<?php

$app['api.key'] = 'your api key, longer is better, try to generate 128 characters - "openssl rand -base64 64" is helpful, but remember to remove the = and + characters';

return $app;
```

```
# put your FTP access data
cp phploy.ini-example phploy.ini
nano phploy.ini # or vim it, nano is easier

# deploy to the remote server
./bin/deploy-to-ftp.sh
```


Paths
=====

This is a list of application routes. Interactive examples are stored in `postman_collection.json` and could be imported into Postman application.

```
# collection management
POST /repository/image/add-by-url
{
    "fileUrl": "http://zsp.net.pl/files/barroness_logo.png",
    "tags": ["user.avatar"] 
}

POST /repository/image/upload
POST /repository/image/exists
POST /repository/image/delete
POST /repository/search/query
{
    "tags": [],
    "limit": 50,
    "offset": 0
}


# technical
GET /repository/stats
GET /repository/routing/map

# authentication
/auth/token/generate
{
   "roles": ['role_1', 'upload.images'],
   "data": {
       "tags": ['article.picture']
   }
}

/jobs/token/expired/clear

# public area
GET /public/download/{imageName}

# public area with temporary token required (use /auth/token/generate to generate temporary tokens)
GET /public/upload/image/form
POST /public/upload/image
{
    "content": "... base64 encoded content ...",
    "fileName": "file-name.jpg",
    "mimeType": "image/jpeg"
}
```

Example forms
=============

You can find some example HTML forms that works out-of-the box with builtin web-server.

At first run:

```
composer install
COMPOSER_PROCESS_TIMEOUT=999999 composer run

and then:
firefox ./test-examples
```

- /test-examples/upload_form.html
- /test-examples/add-by-url-form.html


Console commands
================

Console is placed in `./src/console.php`.
Type the `php ./src/console.php` to see the list of commands.

FAQ
===

1. Why you do not implement the full REST urls?
- It's because our goal is to allow storing files by external URL address,
for example to store an event's background image from facebook
and allow its fast retrieval basing on the URL address

So, the purpose of this files repository is also a files cache,
to store images from external servers that are going to expire soon.
