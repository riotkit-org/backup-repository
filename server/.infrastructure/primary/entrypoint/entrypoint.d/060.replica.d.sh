#!/usr/bin/env bash
#
# Replication support
#  - Waiting for other instance to get up
#  - Verifying if PRIMARY is compatible with our instance
#

set -e

# Optionally: wait for instance to get up
WAIT_FOR_INSTANCE_TIMEOUT=${WAIT_FOR_INSTANCE_TIMEOUT:-300}

if [[ "${WAIT_FOR_INSTACE_URL}" ]] && [[ "${WAIT_FOR_INSTANCE_TOKEN}" ]]; then
    ./bin/console health:wait-for "${WAIT_FOR_INSTACE_URL}" --token="${WAIT_FOR_INSTANCE_TOKEN}" --timeout="${WAIT_FOR_INSTANCE_TIMEOUT}" -vv
fi

if [[ "${REPLICATION_PRIMARY_URL}" ]]; then
    ./bin/console replication:verify-primary
fi
