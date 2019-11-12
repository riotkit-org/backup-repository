#!/bin/bash

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

install() {
    echo " >> Updating the application before starting..."
    su www-data -s /bin/bash -c "cd /var/www/html/ && make install"
}

wait_for_db_to_get_up
correct_permissions
install
