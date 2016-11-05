Wolnościowiec Image Repository
==============================

[![Build Status](https://travis-ci.org/Wolnosciowiec/image-repository.svg?branch=master)](https://travis-ci.org/Wolnosciowiec/image-repository)
[![Code Climate](https://codeclimate.com/github/Wolnosciowiec/image-repository/badges/gpa.svg)](https://codeclimate.com/github/Wolnosciowiec/image-repository)
[![Test Coverage](https://codeclimate.com/github/Wolnosciowiec/image-repository/badges/coverage.svg)](https://codeclimate.com/github/Wolnosciowiec/image-repository/coverage)
[![Issue Count](https://codeclimate.com/github/Wolnosciowiec/image-repository/badges/issue_count.svg)](https://codeclimate.com/github/Wolnosciowiec/image-repository)

Handles images as a node and exposes uploaded images
to end users. Could be used as a cheap cluster of storage
for images, or just as a simple static files storage.

Initially the project was created to store static files
to separate the traffic and conventionally the logic of the application
from static content, and the second target is to provide a possibility
to store the content on a very cheap $1/mo servers.

Requirements for web hosting
============================

- PHP7

Requirements for the build
==========================

- Linux machine
- Composer
- ncftp

Paths
=====

```
# collection management
POST /repository/image/add-by-url
POST /repository/image/upload
POST /repository/image/exists
POST /repository/image/delete

# technical
GET /repository/stats
GET /repository/routing/map

# public area
GET /public/download/{imageName}
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

FAQ
===

1. Why you do not implement the full REST urls?
- It's because our goal is to allow storing files by external URL address,
for example to store an event's background image from facebook
and allow its fast retrieval basing on the URL address

So, the purpose of this files repository is also a files cache,
to store images from external servers that are going to expire soon.