#!/bin/bash

boot_app_in_background() {
    /entrypoint.sh &
}

wait_for_app_to_get_up() {
    max_to_timeout=300

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

install_and_execute_tests() {
    cd /var/www/html
    composer install --dev

    exec ./vendor/bin/codecept run functional --steps
}

entrypoint() {
    if [[ ${API_TESTS} == "true" ]]; then
        set -ex;
        setup_nginx_to_listen_on_development_port
        boot_app_in_background
        wait_for_app_to_get_up
        install_and_execute_tests
        exit $?
    fi

    exec /entrypoint.sh
}

entrypoint
exit $?
