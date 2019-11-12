#!/bin/bash

# overriden_entrypoint.sh extends entrypoint.sh

#
# This file adds functionality, then calls parent entrypoint.sh
#
boot_app_in_background() {
    /entrypoint.sh &
}

exec /entrypoint.sh
