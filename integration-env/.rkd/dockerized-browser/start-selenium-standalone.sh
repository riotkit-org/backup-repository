#!/bin/bash

#
# This file replaces Selenium startup with Google Chrome startup
# We do not need Selenium, we use Chrome directly - but we use Selenium's image, because it is enough mature
# like no other image publicly available
#

# workaround: --remote-debugging-address=0.0.0.0 does not work in Google Chrome
sudo apt-get update
sudo apt-get install socat scrot -y
socat tcp4-listen:9223,reuseaddr,fork,bind=0.0.0.0 tcp:127.0.0.1:9222 &

exec google-chrome --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --no-default-browser-check --no-first-run about:blank
