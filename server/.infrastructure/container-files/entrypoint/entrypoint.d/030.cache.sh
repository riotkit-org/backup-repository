#!/bin/bash

set -e

echo " >> Rendering cache.yaml"
j2 /var/www/html/config/packages/cache.yaml.j2 > /var/www/html/config/packages/cache.yaml
