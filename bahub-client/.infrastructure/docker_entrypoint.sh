#!/bin/bash

echo "" > /.bahub.env
mkdir -p /var/log/bahub
touch /var/log/bahub/bahub.log

# Setup dns. We need internal Docker DNS + external DNS
if [[ $(cat /etc/resolv.conf) != *"1.1.1.1"* ]]; then
    echo "nameserver 1.1.1.1" >> /etc/resolv.conf
    echo "nameserver 8.8.8.8" >> /etc/resolv.conf
fi

# BAHUB_ARGS makes a possibility to inject arguments to each Bahub command call, even if it is called via cron
if [[ "${BAHUB_ARGS}" ]]; then
    echo "BAHUB_ARGS=\"${BAHUB_ARGS}\"" >> /.bahub.env
fi

# Scheduled jobs. Bahub is called from cron asynchronously
if [[ ! -f /cron ]]; then
    echo " !!! Warning !!!:  No /cron file available, should be a crontab-syntax file"
fi

cp /cron /etc/crontab.d/bahub

# On development environment you may not want to override production backups
if [[ ${DISABLE_SCHEDULED_JOBS} == "1" ]]; then
    echo "" > /etc/crontab.d/bahub
fi

supervisord -c /bahub/supervisord.conf
exec tail -f /var/log/bahub/bahub.log
