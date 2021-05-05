Deploying
#########

Proposed infrastructure configuration
*************************************

Backup Repository was created for a purpose of a shared hosting to store backups for non-profits that have self-managed servers all around the world.
Our infrastructure has to have higher limits and timeouts - it is a use case that should fit for almost everyone, by giving example of high scale.

1) Router at the front allows to share IP address by multiple applications
2) Separate NGINX for Backup Repository allows to have clean architecture, NGINX + PHP-FPM should be threaten as a complete application
3) Traffic between Router and Backup Repository NGINX should be without proxy buffering

.. code:: nginx

    proxy_read_timeout 3600s;
    proxy_send_timeout 3600s;
    proxy_buffering off;
    proxy_request_buffering off;
    client_max_body_size 30G;


4) Traffic between Backup Repository NGINX and PHP-FPM should be buffered
5) Traffic between Backup Repository NGINX and PHP-FPM in case of a upload endpoint :class:`^/api/stable/repository/collection/([A-Za-z0-9\-]+)/versions$` should have body buffered to temporary file and passed to application for increased performance


.. code:: nginx

    location ~ ^/api/stable/repository/collection/([A-Za-z0-9\-]+)/versions$ {
        client_body_temp_path /home/backuprepository/var/tmp;
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


.. image:: ../_static/screenshots/proposed-infrastructure.png

Monitoring
**********

Application exposes monitoring endpoints.

1) /health?code=ABC
-------------------

Describes detailed the status of application with its dependent services.
On each run the storage is checked for read and write access, a small file is written and then deleted to perform a test.

**Important:** code parameter should contain a secret defined in :class:`HEALTH_CHECK_CODE` configuration option

.. code:: json

    {
      "status": [
        {
          "storage": true,
          "database": true
        }
      ],
      "messages": [
        {
          "storage": [
            "string"
          ],
          "database": [
            "string"
          ]
        }
      ],
      "global_status": true,
      "ident": [
        "string"
      ]
    }

2) /metrics/backup_repository_report/influxdb?code=DEF
------------------------------------------------------

Returns metrics in InfluxDB Line Protocol format.

**Important:** code parameter should contain a secret defined in :class:`METRICS_CODE` configuration option. **Notice it is different from health check code.**


.. code:: actionscript

    backup_repository_report,base_url=http://localhost:8000,app_env=dev storage_declared_space=0,storage_used_space=0,users_active_accounts=0,users_active_jwt_keys=0,backup_versions=0,backup_collections=0,resources_tags=0 1620237873872992246
