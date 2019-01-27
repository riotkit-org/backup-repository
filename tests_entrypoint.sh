#!/bin/bash

#set -e

echo " >> Migrating the database to the recent version"
su www-data -s /bin/bash -c "cd /var/www/html/ && make migrate"

echo " >> Reconfiguring the application to be running in TEST mode, erasing all data"
su www-data -s /bin/bash -c "cd /var/www/html/ && make _configure_ci_environment _erase_all_data"

echo " >> Starting application in the background"
supervisord -c /etc/supervisor/conf.d/supervisord.conf &
sleep 5

cat /var/www/html/.env

echo " >> Running API tests"
exec newman run /var/www/html/postman-tests.json --timeout 100000 --insecure -e ./postman.ci-environment.json
