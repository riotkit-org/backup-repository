
echo " >> Setting up Sentry.io integration"

if [[ "${SENTRY_DSN}" ]];
then
    echo " .. Sentry.io integration was enabled"
    mv /var/www/html/config/packages/prod/sentry.yaml.disabled /var/www/html/config/packages/prod/sentry.yaml
    su www-data -s /bin/bash -c "cd /var/www/html/ && ./bin/console cache:clear --env=prod"
fi
