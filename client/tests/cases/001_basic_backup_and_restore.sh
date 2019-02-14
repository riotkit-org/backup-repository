#!/bin/bash

source ../init.sh

set -e

# setup
ADMIN_TOKEN=$(generate_admin_token)
echo " >> Token: ${ADMIN_TOKEN}"


#
# Test: "directory" adapter
#
pre_some_local_logs() {
    exec_in_bahub_container 'rm -rf /tmp/temp-backup 2>/dev/null || true'
    exec_in_bahub_container 'mkdir -p /tmp/temp-backup'
    exec_in_bahub_container 'echo "https://iwa-ait.org" > /tmp/temp-backup/social-good-websites'
}
before_restore_some_local_logs() { exec_in_bahub_container 'echo "http://some-wrong-site" > /tmp/temp-backup/social-good-websites'; }
assert_some_local_logs() {
    echo " >> Assert file has original content"

    contents=$(exec_in_bahub_container 'cat /tmp/temp-backup/social-good-websites')

    if [[ ${contents} == *"iwa-ait.org"* ]] && [[ ${contents} != *"some-wrong-site"* ]]; then
        return 0
    fi

    echo " !! File has not been restored"
    return 1
}

#
# Test: "command_output" adapter
#
pre_local_command_output() { exec_in_bahub_container 'rm -f /tmp/bash.restored 2>/dev/null || true'; }
before_local_command_output() { exec_in_bahub_container 'echo "something" > /tmp/bash.restored'; }
assert_local_command_output() {
    echo " >> Assert file has original content (/tmp/bash.restored)"

    if exec_in_bahub_container 'cat /tmp/bash.restored' | grep "something" > /dev/null; then
        echo " !! File has not been restored: ${content}"
        return 1
    fi

    return 0
}

#
# Execution of all cases, one-by-one
#
variants=(some_local_logs local_command_output docker_hot_volumes_example www_docker_offline mysql_docker_single_database)

for variant in ${variants[@]}; do
    echo ""
    echo " ... Testing variant '${variant}'"

    COLLECTION_ID=$(create_collection "Logs" "logs.tar.gz" 4 3GB 14GB)

    if function_exists "pre_${variant}"; then
        set -x; eval "pre_${variant}"; set +x;
    fi

    echo " >> Assert it will be first backup in collection"
    bahub --config /etc/bahub/without_crypto.conf.yaml --debug backup ${variant}
    
    echo " >> Assert submitted backup will be found on listing"
    bahub --config /etc/bahub/without_crypto.conf.yaml list ${variant} | grep '"v1"' > /dev/null

    if function_exists "before_restore_${variant}"; then
        eval "before_restore_${variant}"
    fi

    echo " >> Restoring..."
    bahub --config /etc/bahub/without_crypto.conf.yaml restore ${variant} v1

    if function_exists "assert_${variant}"; then
        eval "assert_${variant}"
    fi

    echo " ... DONE"
    echo ""
done

exit 0