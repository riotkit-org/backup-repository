#!/bin/bash

bahub () {
    ARGS=$@
    exec_in_bahub_container "ADMIN_TOKEN=${ADMIN_TOKEN} COLLECTION_ID=${COLLECTION_ID} bahub-origin ${ARGS}"
}

#
# $1 => description
# $2 => file name
# $3 => length
# $4 => single version size
# $5 => total collection max size
#
create_collection () {
    console "backup:create-collection -d '${1}' -f '${2}' -b '${3}' -o '${4}' -c '${5}'" | perl -pe 's/[^\w.-]+//g'
}

generate_admin_token () {
    console "auth:generate-admin-token" | perl -pe 's/[^\w.-]+//g'
}

console () {
    sudo -E docker-compose exec file-repository \
        /bin/bash -c "./bin/console --env=prod $@"
}

exec_in_bahub_container () {
    sudo -E docker-compose exec bahub /bin/sh -c "$@"
}

read_logs () {
    sudo -E docker-compose exec file-repository /bin/sh -c 'cat ./var/log/prod.log' || true
}

function_exists () {
    if [ -n "$(type -t ${1})" ] && [ "$(type -t ${1})" = function ]; then
        return 0
    fi

    return 1
}

prepare_file_repository_instance () {
    sudo -E docker-compose up -d --force-recreate file-repository
}

prepare_environment() {
    delete_environment
    sudo -E docker-compose up -d --force-recreate
}

delete_environment() {
    sudo -E docker-compose rm -s -v -f
}

wait_for_container_to_start () {
    while ! sudo -E docker-compose exec file-repository curl -s -f http://localhost/health?code=tests_env > /dev/null; do
        sleep 0.1
    done
}

before_running_all_tests () {
    echo " ====> Preparing the environment"
    prepare_environment 2>&1 > /dev/null

    echo " ====> Waiting for application to get up"
    wait_for_container_to_start "file-repository"
}

after_all_tests_passed() {
    sudo -E docker-compose rm -s -v -f
}
