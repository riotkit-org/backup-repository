#!/bin/bash

SLACK_URL=$1
MESSAGE=$2
MESSAGE=${MESSAGE/\[OK\]/:white_check_mark:}
MESSAGE=${MESSAGE/\[FAILURE\]/:exclamation:}

[[ "$SLACK_URL" ]] && echo "{\"text\": \"[Travis-CI] ${MESSAGE}\"}" | curl -H 'Content-type: application/json' -d @- -X POST -s "${SLACK_URL}";

exit 0
