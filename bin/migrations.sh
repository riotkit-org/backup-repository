#!/bin/bash
cd "$( dirname "${BASH_SOURCE[0]}" )"
cd ..

if [[ ! -f ./vendor/bin/phinx ]];
then
    composer install
fi

exec ./vendor/bin/phinx "$@"