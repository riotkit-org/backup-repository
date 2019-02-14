#!/bin/bash

TESTS_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd ${TESTS_DIR}

for file in $(ls ./cases/*.sh | sort); do
    cd ${TESTS_DIR}/cases

    echo " >> Running ${file}"
    bash ../${file}

    if [[ $? != 0 ]]; then
        echo " >> Test ${file} failed."
        exit 1
    fi
done
