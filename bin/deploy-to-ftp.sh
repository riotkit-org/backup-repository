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

echo "==> Uploading all files"
ncftpput -R -v -u $DEPLOYMENT_FTP_USER $DEPLOYMENT_FTP_SERVER $DEPLOYMENT_FTP_SERVER -p $DEPLOYMENT_FTP_DIR ./

echo "==> Removing files that should not be deployed to server"
for fileName in $(cat ./.gitignore); do
    ncftp -u $DEPLOYMENT_FTP_USER $DEPLOYMENT_FTP_SERVER $DEPLOYMENT_FTP_SERVER -p $DEPLOYMENT_FTP_PASSWD <<END_OF_CMD
rm $DEPLOYMENT_FTP_DIR/$fileName
quit
END_OF_CMD
done