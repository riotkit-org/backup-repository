#!/bin/bash

mkdir /home/bahub/logs/ -p
touch /home/bahub/logs/jobber.log
chown root:root /home/bahub/logs/ /home/bahub/.jobber

exec /usr/libexec/jobberrunner "$@"
