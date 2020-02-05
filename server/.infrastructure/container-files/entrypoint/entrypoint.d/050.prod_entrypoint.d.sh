#!/bin/bash -e

wait_for_db_to_get_up() {
    if [[ ${WAIT_FOR_HOST} != "" ]]; then
        IFS=':' read -ra ADDR <<< "${WAIT_FOR_HOST}"
        host=${ADDR[0]}
        port=${ADDR[1]}
        counter=0

        echo " >> Waiting for MySQL server at ${host}:${port} to be up..."
        /opt/riotkit/utils/bin/wait-for-mysql-to-be-ready --host "${host}" --port="${port}" --timeout=300
    fi
}

correct_permissions() {
    echo " >> Correcting permissions"
    mkdir -p /var/www/html/var/uploads /var/www/html/tests/Functional/_output/

    chown www-data:www-data /var/www/html
    chown www-data:www-data /var/www/html/public
    chown www-data:www-data -R /var/www/html/var /var/www/html/tests/Functional/
}

setup_admin_user() {
    echo " >> Preparing admin user if configured via SECURITY_ADMIN_TOKEN environment variable"

    if [[ "${SECURITY_ADMIN_TOKEN}" ]]; then
        echo " >> Setting up default admin token provided with SECURITY_ADMIN_TOKEN environment variable"
        su www-data -s /bin/bash -c "cd /var/www/html/ && ./bin/console auth:generate-admin-token --ignore-error-if-token-exists --id=${SECURITY_ADMIN_TOKEN} " > /dev/null
    fi
}

install() {
    echo " >> Updating the application before starting..."
    su www-data -s /bin/bash -c "cd /var/www/html/ && make install"
}

execute_post_install_commands() {
    echo " >> Executing commands from POST_INSTALL_CMD if set"

    if [[ "${POST_INSTALL_CMD}" != "" ]]; then
        su www-data -s /bin/bash -c "cd /var/www/html; set -xe; ${POST_INSTALL_CMD}"
    fi
}

wait_for_db_to_get_up
correct_permissions
install
setup_admin_user
execute_post_install_commands
