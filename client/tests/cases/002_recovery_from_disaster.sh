#!/bin/bash

source ../init.sh

set -e

# setup
ADMIN_TOKEN=$(generate_admin_token)
echo " >> Token: ${ADMIN_TOKEN}"

# clean up
rm -rf /tmp/bash.restored

COLLECTION_ID=$(create_collection "Logs" "logs.tar.gz" 4 3GB 14GB)

echo " >> Doing a backup"
bahub --config /etc/bahub/without_crypto.conf.yaml --debug backup local_command_output

echo " >> Executing recovery"
bahub --config /etc/bahub/without_crypto.conf.yaml --debug recover plan_2

if sudo docker exec tests_bahub_1 strings /tmp/bash.restored|grep bash > /dev/null; then
    echo " >> Expected to recover a file [ OK ]"
    exit 0
fi

exit 1
