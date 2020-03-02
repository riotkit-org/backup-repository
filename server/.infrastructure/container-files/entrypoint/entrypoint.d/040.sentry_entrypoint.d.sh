#!/bin/bash

echo " >> Setting up Sentry.io integration"

SENTRY_CONFIG_PATH=/var/www/html/config/packages/prod/sentry.yaml

# reset the state
mv "${SENTRY_CONFIG_PATH}" "${SENTRY_CONFIG_PATH}.disabled" 2>/dev/null || true

# enable when required
if [[ "${SENTRY_DSN}" != "" ]]; then
    echo " .. Sentry.io integration was enabled"
    mv "${SENTRY_CONFIG_PATH}.disabled" "${SENTRY_CONFIG_PATH}" || true
    su www-data -s /bin/bash -c "cd /var/www/html/ && ./bin/console cache:clear --env=prod"
fi
