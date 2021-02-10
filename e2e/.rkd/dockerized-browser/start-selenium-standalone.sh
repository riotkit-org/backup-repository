#!/bin/bash

# workaround: --remote-debugging-address=0.0.0.0 does not work in Google Chrome
sudo apt-get update
sudo apt-get install socat -y
socat tcp4-listen:9223,reuseaddr,fork,bind=0.0.0.0 tcp:127.0.0.1:9222 &

exec google-chrome --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --no-default-browser-check --no-first-run
