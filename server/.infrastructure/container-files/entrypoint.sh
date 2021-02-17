#!/bin/bash -e

make_cache() {
    echo " >> Rendering cache.yaml"
    j2 config/packages/cache.yaml.j2 > config/packages/cache.yaml
}

correct_permissions() {
    echo " >> Correcting permissions"
    mkdir -p vendor var
    touch var/data.db

    chown www-data:www-data public vendor
    chown www-data:www-data -R var
}

setup_admin_user() {
    echo " >> Preparing admin user if configured via SECURITY_ADMIN_TOKEN environment variable"

    if [[ "${SECURITY_ADMIN_TOKEN}" ]]; then
        echo " >> Setting up default admin token provided with SECURITY_ADMIN_TOKEN environment variable"
        ./bin/console auth:generate-admin-token --ignore-error-if-token-exists --id=${SECURITY_ADMIN_TOKEN} --expires='+10 years'
    fi
}

execute_post_install_commands() {
    echo " >> Executing commands from POST_INSTALL_CMD if set"

    if [[ "${POST_INSTALL_CMD}" != "" ]]; then
        /bin/bash -c "set -xe; ${POST_INSTALL_CMD}"
    fi
}

install_application() {
    echo " >> Updating the database..."
    ./bin/console doctrine:migrations:migrate -n

    echo " >> Executing composer install..."
    find ./

    if [[ "${CLEAR_CACHE}" == "true" ]]; then
        composer install --no-dev
    else
        composer install --no-scripts --no-dev
    fi
}

make_cache
correct_permissions
setup_admin_user
execute_post_install_commands
install_application

exec multirun -v "nginx" "php-fpm -F -O"
