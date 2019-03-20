#!/bin/bash

echo "" > /.bahub.env

if [[ ! -f /cron ]]; then
    echo " !!! Warning !!!:  No /cron file available, should be a crontab-syntax file"
fi

if [[ "$@" ]]; then
    echo "BAHUB_ARGS=\"$@\"" >> /.bahub.env
fi

# test version of Bahub mounted via volume on test environment (eg. on CI)
if [[ -d /test ]]; then
    cd /test && make install
fi

# on developer environment you may not want to override production backups
if [[ ${DISABLE_SCHEDULED_JOBS} == "1" ]]; then
    echo "" > /cron
    echo "" > /etc/crontabs/root
fi

cp /cron /etc/crontabs/root
supervisord -c /bahub/supervisord.conf

while true; do
    log_file=$(ls /tmp/bahub-*.log|sort -r|head -1)

    if [[ -f ${log_file} ]]; then
        echo " >> Browsing log file ${log_file}"
        tail -f ${log_file}
    fi

    echo " .. Previous log file was deleted or just 'tail' crashed, finding new log file..."
    sleep 5
done
