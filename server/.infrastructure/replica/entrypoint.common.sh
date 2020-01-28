#!/usr/bin/env bash

set -xe

cd /opt/riotkit/app

composer install

# @todo: Wait for REPLICA and PRIMARY

# Optionally: wait for instance to get up
WAIT_FOR_INSTANCE_TIMEOUT=${WAIT_FOR_INSTANCE_TIMEOUT:-300}

if [[ "${WAIT_FOR_INSTACE_URL}" ]] && [[ "${WAIT_FOR_INSTANCE_TOKEN}" ]]; then
    ./bin/console health:wait-for "${WAIT_FOR_INSTACE_URL}" --token="${WAIT_FOR_INSTANCE_TOKEN}" --timeout="${WAIT_FOR_INSTANCE_TIMEOUT}" -vv
fi

# Verify if the primary connection is ok
./bin/console replication:verify
