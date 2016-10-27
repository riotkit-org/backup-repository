#!/bin/bash

if [[ ! -f "./composer.json" ]];
then
    echo "Please run this script from the main directory of the application"
    exit 1
fi

./bin/generate-version-number.sh
composer install

echo -n "Host: "htaccess for Apache, a few new controllers :)
read -s server_host
echo ""

echo -n "User name: "
read -s server_user
echo ""

echo -n "Remote directory: "
read -s server_remote_dir
echo ""

ncftpput -R -v -u $server_user $server_host $server_remote_dir ./