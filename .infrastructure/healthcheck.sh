#!/bin/bash

# when the health check is not configured, then we can use basic checking
if [[ ! "${HEALTH_CHECK_CODE}" ]]; then
    exec curl --fail -s http://localhost
fi

exec curl --fail -s http://localhost/health?code=${HEALTH_CHECK_CODE}
