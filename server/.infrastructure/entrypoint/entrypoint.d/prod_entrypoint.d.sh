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


install() {
    echo " >> Updating the application before starting..."
    su www-data -s /bin/bash -c "cd /var/www/html/ && make install"
}

wait_for_db_to_get_up
install
