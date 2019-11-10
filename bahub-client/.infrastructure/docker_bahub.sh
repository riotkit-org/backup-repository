#!/bin/bash

source /.bahub.env

if [[ ! ${BAHUB_ARGS} ]]; then
    BAHUB_ARGS="--config /bahub.conf.yaml --logs-path /var/log/bahub --logs-file bahub.log"
fi

exec /usr/bin/bahub-origin ${BAHUB_ARGS} "$@"
