#!/bin/bash

composer install

echo -n "Host: "
read -s server_host
echo ""

echo -n "User name: "
read -s server_user
echo ""

echo -n "Remote directory: "
read -s server_remote_dir
echo ""

ncftpput -R -v -u $server_user $server_host $server_remote_dir ./