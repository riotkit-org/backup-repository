#!/bin/bash

if [[ ! -f "./composer.json" ]]; then
    echo "Please run this script from the main directory of the application"
    exit 1
fi

if [[ ! -d .git ]]; then
    echo "Cannot generate a version number, as not in a git repository"
    exit 0
fi

git rev-list --all --count > ./var/version-number
exit $?