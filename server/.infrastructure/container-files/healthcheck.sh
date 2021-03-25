#!/bin/bash

exec /bin/bash -c "./bin/console health:check && curl -f http://localhost > /dev/null"
