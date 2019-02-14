#!/bin/bash

bahub () {
    ARGS=$@
    sudo docker-compose exec \
        bahub /bin/sh -c "ADMIN_TOKEN=${ADMIN_TOKEN} COLLECTION_ID=${COLLECTION_ID} bahub-origin ${ARGS}"
}

#
# $1 => description
# $2 => file name
# $3 => length
# $4 => single version size
# $5 => total collection max size
#
create_collection () {
    sudo docker-compose exec file-repository \
        ./bin/console backup:create-collection -d "${1}" -f "${2}" -b "${3}" -o "${4}" -c "${5}" | perl -pe 's/[^\w.-]+//g'
}

generate_admin_token() {
    sudo docker-compose exec file-repository \
        ./bin/console auth:generate-admin-token | perl -pe 's/[^\w.-]+//g'
}

console () {
    sudo docker-compose exec file-repository \
        ./bin/console "$@" | perl -pe 's/[^\w.-]+//g'
}

exec_in_bahub_container() {
    sudo docker-compose exec bahub /bin/sh -c "$@"
}

function_exists () {
    if [ -n "$(type -t ${1})" ] && [ "$(type -t ${1})" = function ]; then
        return 0
    fi

    return 1
}