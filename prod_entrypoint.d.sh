#!/bin/bash

echo " >> Updating the application before starting..."
su www-data -s /bin/bash -c "cd /var/www/html/ && make deploy"
