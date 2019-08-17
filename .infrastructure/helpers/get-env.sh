#!/bin/bash

#
# get_env method that respects .env file as a fallback
#

ENV_NAME=$1

if [[ -f .env ]] && [[ ${!ENV_NAME} == '' ]]; then
    source .env
fi

echo -n ${!ENV_NAME}
