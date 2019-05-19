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

echo " >> Preparing features"
if [[ ${FEATURES} != "" ]]; then
    IFS=',' read -r -a split_features <<< "${FEATURES}"

    echo " .. Enabled features: ${FEATURES}"

    for feature in "${split_features[@]}"
    do
        echo "   .. Processing feature ${feature}"
        feature_path="/etc/nginx/features/available.d/${feature}.conf"

        if [[ ! -f "${feature_path}" ]]; then
            echo " >> Unsupported NGINX feature: ${feature}"
            exit 1
        fi

        meta=$(head ${feature_path} -n1 | grep "@feature:")
        target_path=$(echo ${meta} | awk '{print $3}')

        if [[ ! "${target_path}" ]]; then
            target_path=$(echo ${meta} | awk '{print $2}')
        fi

        if [[ ! "${target_path}" ]]; then
            echo " >> ERROR: Feature ${feature} does not contain a valid feature header"
            echo "    Example: #@feature: /etc/nginx/features/fastcgi.d"
            exit 1
        fi

        set -x; cp "${feature_path}" "${target_path}/"; set +x;
    done
fi

echo " >> Updating the application before starting..."
su www-data -s /bin/bash -c "cd /var/www/html/ && make deploy"
