#!/bin/bash

source /.bahub.env

if [[ ! ${BAHUB_ARGS} ]]; then
    BAHUB_ARGS="--config /bahub.conf.yaml --logs-path /var/log/bahub --logs-file bahub.log"
fi

USE_PKG="${TEST_MODE_USE_LOCAL_PACKAGE}"
if [[ "${USE_PKG}" == "true" ]] || [[ "${USE_PKG}" == "1" ]] || [[ "${USE_PKG}" == "yes" ]]; then
    PASSED_ARGS="$@"
    set -x; exec /bin/bash -c "cd /bahub/src && python3 -m riotbahub.filerepository.bahub ${BAHUB_ARGS} ${PASSED_ARGS}"
fi

exec /usr/bin/bahub-origin ${BAHUB_ARGS} "$@"
