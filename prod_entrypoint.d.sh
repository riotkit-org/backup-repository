#!/bin/bash

host_is_up () {
    if ! echo "ttttt\n\n" | nc -w 1 "${1}" "${2}" | grep "mysql_native"; then
        return 1
    fi

    return 0
}

if [[ ${WAIT_FOR_HOST} != "" ]]; then
    IFS=':' read -ra ADDR <<< "${WAIT_FOR_HOST}"
    host=${ADDR[0]}
    port=${ADDR[1]}
    counter=0

    echo " Waiting for ${host} on ${port} port to be up..."

    while ! host_is_up ${host} ${port}; do
        sleep 0.2
        counter=$((counter+1))

        if [[ ${counter} -gt 600 ]]; then
            echo " >> Exceeded 2 minutes while waiting for host to be up"
            exit 1
        fi
    done
fi

echo " >> Updating the application before starting..."
su www-data -s /bin/bash -c "cd /var/www/html/ && make deploy"
