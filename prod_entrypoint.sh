#!/bin/bash

#set -e

echo " >> Updating the application before starting..."
su www-data -s /bin/bash -c "cd /var/www/html/ && make deploy"

echo " >> Starting application in the background"
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf &
