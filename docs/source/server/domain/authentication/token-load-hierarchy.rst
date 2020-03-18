Token load hierarchy
====================

Authorization can be provided in multiple ways, including query string, headers and environment variables.
To build a perfect setup it is necessary to know, how the File Repository is fetching the token value, which source is in priority.


Loading priority
----------------

It's a top list, first match wins.

1. **_token in query string** eg. ?_token=xyz is used
2. **token** header
3. **x-auth-token** header
4. **FILE_REPOSITORY_TOKEN** environment variable


Use cases: Static assets serving
--------------------------------

Best practice is to have each file, each collection secured with a token.
You can generate a **viewer token**, and set it as an environment variable on given endpoints, or on whole application.

Using NGINX, Apache 2 or other webserver you can deny access to some routes, on other routes set a default access token - by enforcing a header or environment variable.
The webserver proxies also gives a possibility to strip out request data, for example the headers and query string parts.

