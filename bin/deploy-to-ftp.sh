#!/bin/bash

if [[ ! -f "./composer.json" ]];
then
    echo "Please run this script from the main directory of the application"
    exit 1
fi

if [[ ! -f "./config/DEPLOYMENT_FTP" ]];
then
    echo "You need to create a config/DEPLOYMENT_FTP file from the template"
    exit 1
fi

source ./config/DEPLOYMENT_FTP
./bin/generate-version-number.sh
composer install

echo "==> Clearing up dev cache"
rm -rf ./var/cache/profiler/
rm ./var/cache/logs/silex_dev.log
rm ./config/database_prod.sqlite3 2>/dev/null

echo "==> Uploading all files"
ncftpput -u $DEPLOYMENT_FTP_USER -R -v -p $DEPLOYMENT_FTP_PASSWD $DEPLOYMENT_FTP_SERVER $DEPLOYMENT_FTP_DIR ./

echo "==> Removing files that should not be deployed to server"
for fileName in $(cat ./.production-ignore); do
    echo "~ rm $DEPLOYMENT_FTP_DIR/$fileName"

    ncftp -u $DEPLOYMENT_FTP_USER $DEPLOYMENT_FTP_SERVER $DEPLOYMENT_FTP_SERVER -p $DEPLOYMENT_FTP_PASSWD <<END_OF_CMD
rm $DEPLOYMENT_FTP_DIR/$fileName
quit
END_OF_CMD
done