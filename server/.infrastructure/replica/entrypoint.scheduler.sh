#!/usr/bin/env bash

SLEEP_TIME=${SLEEP_TIME:-5}

cd /opt/riotkit/app || exit 1
/entrypoint.common.sh || exit 1

while true; do
    sleep "${SLEEP_TIME}"
    # shellcheck disable=SC2068
    ./bin/console replication:workers:collect-stream ${@}
done
