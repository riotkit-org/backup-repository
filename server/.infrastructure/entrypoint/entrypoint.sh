#!/bin/bash

# overriden_entrypoint.sh extends entrypoint.sh

#
# This file adds functionality, then calls parent entrypoint.sh
#
boot_app_in_background() {
    /entrypoint.sh &
}

wait_for_app_to_get_up() {
    max_to_timeout=60

    while ! curl -s http://localhost | grep "Hello"; do
        sleep 1
        max_to_timeout=$((max_to_timeout - 1))

        if [[ ${max_to_timeout} == 0 ]]; then
            echo " >> Timeout, while waiting for application to get up"
            exit 1
        fi
    done
}

setup_nginx_to_listen_on_development_port() {
    sed -i -e 's/listen 80/listen 8000; listen 80/g' /etc/nginx/nginx.conf
}

set_application_in_test_mode() {
    sed -i -e 's/APP_ENV=prod/APP_ENV=test/g' /var/www/html/.env
}

#
# There are no separate containers for PROD and TEST
#
execute_tests() {
    cd /var/www/html
    SYMFONY_DEPRECATIONS_HELPER=disabled=1 ./vendor/bin/codecept run functional --steps
}

# This means if we want to execute the tests we need to install dependencies there.
install_dev_environment() {
    echo " >> Updating the application before starting..."
    su www-data -s /bin/bash -c "cd /var/www/html/ && make install"
}


if [[ ${API_TESTS} == "true" ]]; then
    set -ex;
    setup_nginx_to_listen_on_development_port
    set_application_in_test_mode
    install_dev_environment
    boot_app_in_background
    wait_for_app_to_get_up
    execute_tests
    exit $?
fi


exec /entrypoint.sh
