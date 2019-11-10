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
        /bin/bash -c "cd /var/www/html && ./bin/console backup:create-collection -d '${1}' -f '${2}' -b '${3}' -o '${4}' -c '${5}'" | perl -pe 's/[^\w.-]+//g'
}

generate_admin_token () {
    sudo docker-compose exec file-repository \
        /bin/bash -c "cd /var/www/html && ./bin/console --env=prod auth:generate-admin-token" | perl -pe 's/[^\w.-]+//g'
}

console () {
    sudo docker-compose exec file-repository \
        /bin/bash -c "./bin/console --env=prod $@"
}

exec_in_bahub_container () {
    sudo docker-compose exec bahub /bin/sh -c "$@"
}

read_logs () {
    sudo docker-compose exec file-repository /bin/sh -c 'cat ./var/log/prod.log' || true
}

function_exists () {
    if [ -n "$(type -t ${1})" ] && [ "$(type -t ${1})" = function ]; then
        return 0
    fi

    return 1
}

prepare_file_repository_instance () {
    sudo docker-compose up -d --force-recreate file-repository
}

prepare_environment() {
    sudo docker-compose rm -s -v -f
    sudo docker-compose up -d --force-recreate
}

wait_for_container_to_start () {
    while ! sudo docker-compose exec file-repository curl http://localhost |grep "Hello, welcome" > /dev/null; do
        sleep 0.1
    done
}

before_running_all_tests () {
    echo " ====> Preparing the environment"
    prepare_environment 2>&1 > /dev/null

    echo " ====> Waiting for application to get up"
    wait_for_container_to_start "file-repository"
}
