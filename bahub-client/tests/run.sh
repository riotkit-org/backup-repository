#!/bin/bash

TESTS_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd ${TESTS_DIR}

source init.sh
before_running_all_tests

echo ""
echo " ===> Starting all tests"
echo ""

for file in $(ls ./cases/*.sh | sort); do
    cd ${TESTS_DIR}/cases

    echo " >> Running ${file}"
    bash ../${file}

    if [[ $? != 0 ]]; then
        echo " >> Test ${file} failed."
        read_logs
        exit 1
    fi
done

after_all_tests_passed

echo " .. ALL TESTS PASSED"
exit 0
