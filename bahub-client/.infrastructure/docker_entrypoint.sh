#!/bin/bash

echo "" > /.bahub.env
mkdir -p /var/log/bahub
touch /var/log/bahub/bahub.log

if [[ ! -f /cron ]]; then
    echo " !!! Warning !!!:  No /cron file available, should be a crontab-syntax file"
fi

# setup dns
if [[ $(cat /etc/resolv.conf) != *"1.1.1.1"* ]]; then
    echo "nameserver 1.1.1.1" >> /etc/resolv.conf
    echo "nameserver 8.8.8.8" >> /etc/resolv.conf
fi

if [[ "$@" ]]; then
    echo "BAHUB_ARGS=\"$@\"" >> /.bahub.env
fi

# test version of Bahub mounted via volume on test environment (eg. on CI)
if [[ -d /test ]]; then
    cd /test && make install
fi

cp /cron /etc/crontabs/root

# on developer environment you may not want to override production backups
if [[ ${DISABLE_SCHEDULED_JOBS} == "1" ]]; then
    echo "" > /etc/crontabs/root
fi

supervisord -c /bahub/supervisord.conf

exec tail -f /var/log/bahub/bahub.log
