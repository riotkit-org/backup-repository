#!/bin/bash

set -e

make_cache() {
    echo " >> Rendering cache.yaml"
    j2 config/packages/cache.yaml.j2 > config/packages/cache.yaml
}

create_dirs() {
    echo " >> Creating directories"
    mkdir -p vendor var
    mkdir -p /home/backuprepository/var/tmp || true
    chown www-data:www-data /home/backuprepository/var/tmp
}

setup_admin_user() {
    echo " >> Preparing admin user if configured via SECURITY_ADMIN_TOKEN environment variable"

    # @todo: Refactor to admin account
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
    echo " >> Executing composer install (APP_ENV=${APP_ENV})..."

    # --no-scripts: Do not clear cache if not needed (containers can be running in a cluster of multiple instances, why to clear cache of all instances?)
    # --no-progress: It is non-interactive session, we do not need progress
    COMPOSER_ARGS=" --no-scripts --no-progress "

    if [[ $APP_ENV == "prod" ]]; then
        # on production mode we do not need WebProfilerBundle and others. But on test/dev mode we need to install them
        # because application would not work at all
        COMPOSER_ARGS="${COMPOSER_ARGS} --no-dev "
    fi

    echo " >> ${COMPOSER_ARGS}"
    eval "composer install ${COMPOSER_ARGS}"

    echo " >> Updating the database..."
    ./bin/console health:wait-for:database --timeout ${WAIT_FOR_DB_TIMEOUT}
    ./bin/console doctrine:migrations:migrate -n

    if [[ "${CLEAR_CACHE}" == "true" ]]; then
        ./bin/console cache:clear --env=${APP_ENV}
    fi
}

setup_jwt() {
    echo " >> Checking GPG keypair"

    if [[ "$APP_ENV" == "prod" ]]; then
        if ! df | grep "jwt" > /dev/null; then
            echo " >> On production the keys should be mounted as a volume, sorry, cannot continue"
            echo " >> Consequence of not keeping the keys can be catastrophic - after container restart all JWTs would be revoked"
            exit 1
        fi
    fi

    if [[ ! "${JWT_PASSPHRASE}" ]]; then
        echo " >> ERROR: JWT_PASSPHRASE must be supplied!"
        exit 1
    fi

    if [[ ! -f config/jwt/private.pem ]]; then
        echo " >> Generating GPG keypair (as it is not present)"
        export JWT_PASSPHRASE

        # first time we need to use openssl to generate keys, because the console is useless without keys
        # (there is a validation if the keys are present)
        openssl genpkey -out $(pwd)/config/jwt/private.pem -aes256 -pass "pass:$JWT_PASSPHRASE" -algorithm rsa -pkeyopt rsa_keygen_bits:4096
        openssl pkey -in $(pwd)/config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:$JWT_PASSPHRASE
    fi
}

make_cache
create_dirs
setup_jwt
install_application
setup_admin_user
execute_post_install_commands

appLogPath="/home/backuprepository/var/log/${APP_ENV}.log"
touch "${appLogPath}"
touch "/var/log/nginx/error.log"
touch "/var/log/nginx/access.log"

# execute original docker-php-app's (base image) entrypoint
exec /usr/bin/entrypoint.sh multirun -v "nginx" "php-fpm -F -O" "tail -f ${appLogPath}" "tail -f /var/log/nginx/error.log" "tail -f /var/log/nginx/access.log"
