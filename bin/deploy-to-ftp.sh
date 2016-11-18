#!/bin/bash

if [[ ! -f "./composer.json" ]];
then
    echo "Please run this script from the main directory of the application"
    exit 1
fi

if [[ ! -f "./phploy.ini" ]];
then
    echo "You need to create a phploy.ini file from the template"
    exit 1
fi

command=""

if type phploy >/dev/null 2>&1; then
    command="phploy"
elif [[ -f ~/.composer/vendor/banago/phploy/bin/phploy.phar ]]; then
    command="~/.composer/vendor/banago/phploy/bin/phploy.phar"
fi

if [[ ! $command ]]; then
    echo "Cannot find a phploy installed, you can install it via composer globally"
    echo "See: https://github.com/banago/PHPloy"
fi

eval $command