#!/bin/bash

mkdir -p /var/log/bahub
source /.bahub.env

if [[ ! ${BAHUB_ARGS} ]]; then
    BAHUB_ARGS="--config /bahub.conf.yaml --logs-path /var/log/bahub"
fi

exec /usr/bin/bahub-origin ${BAHUB_ARGS} "$@"
