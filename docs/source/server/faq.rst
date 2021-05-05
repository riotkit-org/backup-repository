FAQ
###

1. Cannot upload large files - upload hangs
-------------------------------------------

There are limitations in PHP-FPM and PHP. Workaround is to setup a reverse proxy to handle file upload and pass already uploaded files to PHP.
Backup Repository implements such reverse proxy trick described there https://stackoverflow.com/questions/44371643/nginx-php-failing-with-large-file-uploads-over-6-gb/44751210#44751210

Official Backup Repository docker container have this configuration already prepared.

.. code:: nginx

    server {
    # ...

        location ~ ^/api/stable/repository/collection/([A-Za-z0-9\-]+)/versions$ {
            client_body_temp_path /home/backuprepository/var/tmp;  # the temporary directory has to match directory specified in "REVPROXY_STORAGE_DIR"
            client_body_in_file_only   clean;
            client_body_buffer_size    1M;

            fastcgi_pass_request_body          off;
            fastcgi_pass_request_headers       on;

            include /etc/nginx/fastcgi.conf;
            fastcgi_param  X_INTERNAL_FILENAME $request_body_file;
            fastcgi_param  SCRIPT_FILENAME     $document_root/index.php;
            fastcgi_param  SCRIPT_NAME         index.php;

            fastcgi_index                      index.php;
            fastcgi_pass                       localhost:9000;

            break;
        }

    # ...
    }


2. Cannot upload large files - 502 bad gateway / 504 gateway timeout
--------------------------------------------------------------------

Your reverse proxy, a firewall or some other layer behind application is setting a timeout.

a) If you use NGINX behind official Backup Repository docker container

.. code:: nginx

    proxy_read_timeout 3600s;
    proxy_send_timeout 3600s;
    proxy_buffering off;
    proxy_request_buffering off;
    client_max_body_size 15G;


b) If you have Backup Repository's NGINX exposed directly - PHP-FPM + NGINX without additional NGINX router

.. code:: nginx

    fastcgi_read_timeout 3600s;
    fastcgi_send_timeout 3600s;
